<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SearchFolderService
{
    protected $defaultSearchFolders = [
        [
            'name' => 'Unread Messages',
            'description' => 'All unread emails',
            'icon' => 'envelope',
            'color' => '#3B82F6',
            'search_criteria' => [
                'is_read' => false,
                'folders' => ['inbox']
            ]
        ],
        [
            'name' => 'Flagged',
            'description' => 'All flagged emails',
            'icon' => 'flag',
            'color' => '#EF4444',
            'search_criteria' => [
                'is_flagged' => true
            ]
        ],
        [
            'name' => 'With Attachments',
            'description' => 'Emails with attachments',
            'icon' => 'paper-clip',
            'color' => '#10B981',
            'search_criteria' => [
                'has_attachments' => true
            ]
        ],
        [
            'name' => 'From Me',
            'description' => 'Emails I sent',
            'icon' => 'user',
            'color' => '#8B5CF6',
            'search_criteria' => [
                'from_self' => true
            ]
        ],
        [
            'name' => 'Last 7 Days',
            'description' => 'Recent emails from the past week',
            'icon' => 'calendar',
            'color' => '#F59E0B',
            'search_criteria' => [
                'date_range' => '7_days'
            ]
        ]
    ];
    
    /**
     * Get user's search folders
     */
    public function getUserSearchFolders($userId, $includeCount = true)
    {
        $folders = DB::table('search_folders')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('order_index')
            ->orderBy('name')
            ->get();
            
        if ($includeCount) {
            foreach ($folders as $folder) {
                $folder->email_count = $this->getSearchFolderCount($folder->id, $userId);
            }
        }
        
        return $folders;
    }
    
    /**
     * Create a search folder
     */
    public function createSearchFolder($userId, $data)
    {
        try {
            // Validate search criteria
            if (!$this->validateSearchCriteria($data['search_criteria'])) {
                throw new \Exception('Invalid search criteria');
            }
            
            $maxOrder = DB::table('search_folders')
                ->where('user_id', $userId)
                ->max('order_index') ?? 0;
            
            $folderId = DB::table('search_folders')->insertGetId([
                'user_id' => $userId,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'icon' => $data['icon'] ?? 'search',
                'color' => $data['color'] ?? '#6B7280',
                'search_criteria' => json_encode($data['search_criteria']),
                'order_index' => $maxOrder + 1,
                'show_in_sidebar' => $data['show_in_sidebar'] ?? true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Cache initial count
            $this->updateSearchFolderCache($folderId, $userId);
            
            return [
                'success' => true,
                'folder_id' => $folderId,
                'message' => 'Search folder created successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create search folder: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get emails matching search folder criteria
     */
    public function getSearchFolderEmails($folderId, $userId, $page = 1, $perPage = 20)
    {
        $folder = DB::table('search_folders')
            ->where('id', $folderId)
            ->where('user_id', $userId)
            ->first();
            
        if (!$folder) {
            return null;
        }
        
        $criteria = json_decode($folder->search_criteria, true);
        $query = $this->buildSearchQuery($criteria, $userId);
        
        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }
    
    /**
     * Build search query based on criteria
     */
    protected function buildSearchQuery($criteria, $userId)
    {
        $query = DB::table('emails')
            ->where(function($q) use ($userId) {
                $q->where('recipient_id', $userId)
                  ->orWhere('sender_id', $userId);
            });
            
        // Basic filters
        if (isset($criteria['is_read'])) {
            $query->where('is_read', $criteria['is_read']);
        }
        
        if (isset($criteria['is_flagged'])) {
            $query->where('is_flagged', $criteria['is_flagged']);
        }
        
        if (isset($criteria['has_attachments'])) {
            $query->where('has_attachments', $criteria['has_attachments']);
        }
        
        if (isset($criteria['is_focused'])) {
            $query->where('is_focused', $criteria['is_focused']);
        }
        
        // Folder filter
        if (isset($criteria['folders']) && is_array($criteria['folders'])) {
            $query->whereIn('folder', $criteria['folders']);
        }
        
        // From self
        if (isset($criteria['from_self']) && $criteria['from_self']) {
            $query->where('sender_id', $userId);
        }
        
        // Date range
        if (isset($criteria['date_range'])) {
            $date = $this->getDateFromRange($criteria['date_range']);
            if ($date) {
                $query->where('created_at', '>=', $date);
            }
        }
        
        // Custom date range
        if (isset($criteria['date_from'])) {
            $query->where('created_at', '>=', $criteria['date_from']);
        }
        
        if (isset($criteria['date_to'])) {
            $query->where('created_at', '<=', $criteria['date_to']);
        }
        
        // Text search
        if (isset($criteria['search_text'])) {
            $searchText = '%' . $criteria['search_text'] . '%';
            $query->where(function($q) use ($searchText) {
                $q->where('subject', 'LIKE', $searchText)
                  ->orWhere('body', 'LIKE', $searchText)
                  ->orWhere('sender_email', 'LIKE', $searchText)
                  ->orWhere('recipient_email', 'LIKE', $searchText);
            });
        }
        
        // Sender filter
        if (isset($criteria['from_email'])) {
            $query->where('sender_email', 'LIKE', '%' . $criteria['from_email'] . '%');
        }
        
        // Recipient filter
        if (isset($criteria['to_email'])) {
            $query->where('recipient_email', 'LIKE', '%' . $criteria['to_email'] . '%');
        }
        
        // Has specific label
        if (isset($criteria['has_label'])) {
            $query->whereExists(function($q) use ($criteria) {
                $q->select(DB::raw(1))
                  ->from('email_label')
                  ->whereColumn('email_label.email_id', 'emails.id')
                  ->where('email_label.label_id', $criteria['has_label']);
            });
        }
        
        // Priority filter
        if (isset($criteria['priority'])) {
            $query->where('priority', $criteria['priority']);
        }
        
        // Size filter
        if (isset($criteria['min_size'])) {
            $query->where('size', '>=', $criteria['min_size']);
        }
        
        if (isset($criteria['max_size'])) {
            $query->where('size', '<=', $criteria['max_size']);
        }
        
        // Exclude trash unless specifically requested
        if (!isset($criteria['include_trash']) || !$criteria['include_trash']) {
            $query->where('folder', '!=', 'trash');
        }
        
        return $query;
    }
    
    /**
     * Get date from range string
     */
    protected function getDateFromRange($range)
    {
        switch ($range) {
            case 'today':
                return now()->startOfDay();
            case 'yesterday':
                return now()->subDay()->startOfDay();
            case '3_days':
                return now()->subDays(3)->startOfDay();
            case '7_days':
                return now()->subDays(7)->startOfDay();
            case '30_days':
                return now()->subDays(30)->startOfDay();
            case 'this_month':
                return now()->startOfMonth();
            case 'last_month':
                return now()->subMonth()->startOfMonth();
            case 'this_year':
                return now()->startOfYear();
            default:
                return null;
        }
    }
    
    /**
     * Get search folder count
     */
    public function getSearchFolderCount($folderId, $userId)
    {
        $cacheKey = "search_folder_count_{$folderId}_{$userId}";
        
        return Cache::remember($cacheKey, 300, function() use ($folderId, $userId) {
            $folder = DB::table('search_folders')
                ->where('id', $folderId)
                ->where('user_id', $userId)
                ->first();
                
            if (!$folder) {
                return 0;
            }
            
            $criteria = json_decode($folder->search_criteria, true);
            return $this->buildSearchQuery($criteria, $userId)->count();
        });
    }
    
    /**
     * Update search folder cache
     */
    public function updateSearchFolderCache($folderId, $userId)
    {
        $count = $this->getSearchFolderCount($folderId, $userId);
        
        DB::table('search_folders')
            ->where('id', $folderId)
            ->update([
                'cached_count' => $count,
                'last_cached_at' => now()
            ]);
            
        return $count;
    }
    
    /**
     * Validate search criteria
     */
    protected function validateSearchCriteria($criteria)
    {
        if (!is_array($criteria) || empty($criteria)) {
            return false;
        }
        
        // At least one search criterion must be present
        $validCriteria = [
            'is_read', 'is_flagged', 'has_attachments', 'is_focused',
            'folders', 'from_self', 'date_range', 'date_from', 'date_to',
            'search_text', 'from_email', 'to_email', 'has_label',
            'priority', 'min_size', 'max_size'
        ];
        
        $hasValidCriterion = false;
        foreach ($validCriteria as $criterion) {
            if (isset($criteria[$criterion])) {
                $hasValidCriterion = true;
                break;
            }
        }
        
        return $hasValidCriterion;
    }
    
    /**
     * Update search folder
     */
    public function updateSearchFolder($folderId, $userId, $data)
    {
        try {
            $folder = DB::table('search_folders')
                ->where('id', $folderId)
                ->where('user_id', $userId)
                ->first();
                
            if (!$folder) {
                throw new \Exception('Search folder not found');
            }
            
            $updateData = [];
            
            if (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }
            
            if (isset($data['description'])) {
                $updateData['description'] = $data['description'];
            }
            
            if (isset($data['icon'])) {
                $updateData['icon'] = $data['icon'];
            }
            
            if (isset($data['color'])) {
                $updateData['color'] = $data['color'];
            }
            
            if (isset($data['search_criteria'])) {
                if (!$this->validateSearchCriteria($data['search_criteria'])) {
                    throw new \Exception('Invalid search criteria');
                }
                $updateData['search_criteria'] = json_encode($data['search_criteria']);
            }
            
            if (isset($data['show_in_sidebar'])) {
                $updateData['show_in_sidebar'] = $data['show_in_sidebar'];
            }
            
            $updateData['updated_at'] = now();
            
            DB::table('search_folders')
                ->where('id', $folderId)
                ->update($updateData);
                
            // Update cache
            $this->updateSearchFolderCache($folderId, $userId);
            
            return [
                'success' => true,
                'message' => 'Search folder updated successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update search folder: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete search folder
     */
    public function deleteSearchFolder($folderId, $userId)
    {
        try {
            $deleted = DB::table('search_folders')
                ->where('id', $folderId)
                ->where('user_id', $userId)
                ->delete();
                
            // Clear cache
            Cache::forget("search_folder_count_{$folderId}_{$userId}");
            
            return [
                'success' => $deleted > 0,
                'message' => $deleted > 0 ? 'Search folder deleted' : 'Search folder not found'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete search folder: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create default search folders for user
     */
    public function createDefaultSearchFolders($userId)
    {
        foreach ($this->defaultSearchFolders as $index => $folder) {
            $this->createSearchFolder($userId, array_merge($folder, [
                'order_index' => $index
            ]));
        }
    }
    
    /**
     * Reorder search folders
     */
    public function reorderSearchFolders($userId, $folderOrder)
    {
        try {
            foreach ($folderOrder as $index => $folderId) {
                DB::table('search_folders')
                    ->where('id', $folderId)
                    ->where('user_id', $userId)
                    ->update(['order_index' => $index]);
            }
            
            return [
                'success' => true,
                'message' => 'Search folders reordered successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to reorder search folders: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to reorder search folders'
            ];
        }
    }
}
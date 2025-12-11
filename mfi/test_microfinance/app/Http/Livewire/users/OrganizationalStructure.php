<?php

namespace App\Http\Livewire\Users;

use App\Models\Department;
use App\Models\Role;
use App\Models\Permission;
use Livewire\Component;

class OrganizationalStructure extends Component
{
    public $departments;
    public $roles;
    public $permissions;
    public $totalRoles;
    public $totalDepartments;
    public $totalPermissions;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        // Load departments with their roles
        $this->departments = Department::with(['roles'])
            ->orderBy('level')
            ->orderBy('department_name')
            ->get();

        // Load roles with their departments
        $this->roles = Role::with(['department'])
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        // Load permissions
        $this->permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get();

        // Calculate totals
        $this->totalRoles = $this->roles->count();
        $this->totalDepartments = $this->departments->count();
        $this->totalPermissions = $this->permissions->count();
    }

    public function getGovernanceDepartments()
    {
        return $this->departments->filter(function ($department) {
            return in_array($department->department_code, ['AGM', 'BOD', 'SUP', 'EXT', 'AUD', 'FIN', 'CRD', 'EDU', 'ICT', 'ETH']);
        });
    }

    public function getManagementDepartments()
    {
        return $this->departments->filter(function ($department) {
            return in_array($department->department_code, ['CEO']);
        });
    }

    public function getOperationalDepartments()
    {
        return $this->departments->filter(function ($department) {
            return in_array($department->department_code, ['OPS', 'FIN', 'CRD', 'RCD', 'HRA', 'MMR']);
        });
    }

    public function getDepartmentHierarchy()
    {
        $hierarchy = [];
        
        // AGM Level
        $agm = $this->departments->where('department_code', 'AGM')->first();
        if ($agm) {
            $hierarchy['AGM'] = [
                'department' => $agm,
                'children' => []
            ];

            // Board of Directors
            $bod = $this->departments->where('department_code', 'BOD')->first();
            if ($bod) {
                $hierarchy['AGM']['children']['BOD'] = [
                    'department' => $bod,
                    'children' => []
                ];

                // Board Sub-Committees
                $subCommittees = $this->departments->whereIn('department_code', ['AUD', 'FIN', 'CRD', 'EDU', 'ICT', 'ETH']);
                foreach ($subCommittees as $committee) {
                    $hierarchy['AGM']['children']['BOD']['children'][$committee->department_code] = [
                        'department' => $committee,
                        'children' => []
                    ];
                }
            }

            // Supervisory Committee
            $supervisory = $this->departments->where('department_code', 'SUP')->first();
            if ($supervisory) {
                $hierarchy['AGM']['children']['SUP'] = [
                    'department' => $supervisory,
                    'children' => []
                ];
            }

            // External Auditor
            $external = $this->departments->where('department_code', 'EXT')->first();
            if ($external) {
                $hierarchy['AGM']['children']['EXT'] = [
                    'department' => $external,
                    'children' => []
                ];
            }
        }

        // CEO Level
        $ceo = $this->departments->where('department_code', 'CEO')->first();
        if ($ceo) {
            $hierarchy['CEO'] = [
                'department' => $ceo,
                'children' => []
            ];

            // Operational Departments
            $operationalDepts = $this->departments->whereIn('department_code', ['OPS', 'FIN', 'CRD', 'RCD', 'HRA', 'MMR']);
            foreach ($operationalDepts as $dept) {
                $hierarchy['CEO']['children'][$dept->department_code] = [
                    'department' => $dept,
                    'children' => []
                ];
            }
        }

        return $hierarchy;
    }

    public function getRoleStatistics()
    {
        $stats = [
            'governance' => 0,
            'management' => 0,
            'operations' => 0,
            'finance' => 0,
            'credit' => 0,
            'risk' => 0,
            'hr' => 0,
            'marketing' => 0
        ];

        foreach ($this->roles as $role) {
            if ($role->department) {
                $deptCode = $role->department->department_code;
                
                if (in_array($deptCode, ['AGM', 'BOD', 'SUP', 'EXT', 'AUD', 'FIN', 'CRD', 'EDU', 'ICT', 'ETH'])) {
                    $stats['governance']++;
                } elseif ($deptCode === 'CEO') {
                    $stats['management']++;
                } elseif ($deptCode === 'OPS') {
                    $stats['operations']++;
                } elseif ($deptCode === 'FIN') {
                    $stats['finance']++;
                } elseif ($deptCode === 'CRD') {
                    $stats['credit']++;
                } elseif ($deptCode === 'RCD') {
                    $stats['risk']++;
                } elseif ($deptCode === 'HRA') {
                    $stats['hr']++;
                } elseif ($deptCode === 'MMR') {
                    $stats['marketing']++;
                }
            }
        }

        return $stats;
    }

    public function getPermissionStatistics()
    {
        $stats = [
            'governance' => 0,
            'operations' => 0,
            'finance' => 0,
            'credit' => 0,
            'risk' => 0,
            'hr' => 0,
            'marketing' => 0,
            'system' => 0
        ];

        foreach ($this->permissions as $permission) {
            $module = $permission->module;
            
            if (isset($stats[$module])) {
                $stats[$module]++;
            }
        }

        return $stats;
    }

    public function render()
    {
        return view('livewire.users.organizational-structure');
    }
} 
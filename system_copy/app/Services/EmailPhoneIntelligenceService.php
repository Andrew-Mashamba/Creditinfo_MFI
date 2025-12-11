<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Email and Phone Intelligence Service
 *
 * Validates and scores emails and phone numbers for fraud detection
 * Features:
 * - Disposable email detection
 * - Free email provider identification
 * - Phone number validation
 * - VoIP detection
 * - Risk scoring
 */
class EmailPhoneIntelligenceService
{
    /**
     * Validate and score email address
     *
     * @param string $email
     * @return array Email intelligence data
     */
    public function validateEmail(string $email): array
    {
        $email = strtolower(trim($email));

        // Basic validation
        $isValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;

        if (!$isValid) {
            return [
                'is_valid' => false,
                'risk_score' => 100,
                'reason' => 'Invalid email format',
            ];
        }

        // Extract domain
        $domain = substr(strrchr($email, "@"), 1);

        // Perform checks
        $isDisposable = $this->isDisposableEmail($domain);
        $isFreeProvider = $this->isFreeEmailProvider($domain);
        $domainAge = $this->getDomainAge($domain);

        // Calculate risk score
        $riskScore = $this->calculateEmailRisk($email, $domain, $isDisposable, $isFreeProvider, $domainAge);

        return [
            'is_valid' => true,
            'email' => $email,
            'domain' => $domain,
            'is_disposable' => $isDisposable,
            'is_free_provider' => $isFreeProvider,
            'domain_age_days' => $domainAge,
            'risk_score' => $riskScore,
            'risk_level' => $this->getRiskLevel($riskScore),
            'warnings' => $this->getEmailWarnings($isDisposable, $isFreeProvider, $domainAge),
        ];
    }

    /**
     * Check if email domain is disposable/temporary
     */
    protected function isDisposableEmail(string $domain): bool
    {
        // Cache disposable domains list for 24 hours
        $disposableDomains = Cache::remember('disposable_email_domains', 86400, function () {
            return [
                // Temporary email providers
                'tempmail.com', 'temp-mail.org', 'temp-mail.io', 'temp-mail.net',
                'guerrillamail.com', 'guerrillamail.net', 'guerrillamail.org',
                '10minutemail.com', '10minutemail.net', '10minutemail.org',
                'mailinator.com', 'mailinator.net', 'mailinator2.com',
                'throwaway.email', 'throwawayemail.com',
                'getnada.com', 'getairmail.com',
                'maildrop.cc', 'sharklasers.com',
                'yopmail.com', 'yopmail.fr', 'yopmail.net',
                'trashmail.com', 'trashmail.net',
                'dispostable.com', 'discardmail.com',
                'spamgourmet.com', 'spamgourmet.net',
                'mintemail.com', 'mytemp.email',
                'mohmal.com', 'tempmail.net',
                'burnermail.io', 'anonymousemail.me',
                'fakeinbox.com', 'fakemail.net',
                'emailondeck.com', 'emailfake.com',
                'emltmp.com', 'tempr.email',
                'disposablemail.com', 'disposableemail.com',
                'inboxbear.com', 'mailsac.com',
                'mailcatch.com', 'mailpoof.com',
                'mailslurp.com', 'maildrop.cf',
                // Add more from https://github.com/disposable-email-domains/disposable-email-domains
            ];
        });

        return in_array($domain, $disposableDomains);
    }

    /**
     * Check if email is from free provider
     */
    protected function isFreeEmailProvider(string $domain): bool
    {
        $freeProviders = [
            // Major free providers
            'gmail.com', 'googlemail.com',
            'yahoo.com', 'yahoo.co.uk', 'yahoo.fr', 'yahoo.de',
            'hotmail.com', 'hotmail.co.uk', 'hotmail.fr',
            'outlook.com', 'outlook.fr', 'live.com', 'msn.com',
            'aol.com', 'aim.com',
            'icloud.com', 'me.com', 'mac.com',
            'mail.com', 'email.com',
            'gmx.com', 'gmx.net', 'gmx.de',
            'protonmail.com', 'protonmail.ch', 'pm.me',
            'zoho.com', 'zohomail.com',
            'fastmail.com', 'fastmail.fm',
            'mail.ru', 'yandex.com', 'yandex.ru',
            'inbox.com', 'inbox.lv',
            'tutanota.com', 'tutanota.de',
            'hushmail.com', 'hushmail.me',
        ];

        return in_array($domain, $freeProviders);
    }

    /**
     * Get domain age (simplified - would need WHOIS lookup for real data)
     */
    protected function getDomainAge(string $domain): ?int
    {
        // In production, use WHOIS API or domain age API
        // For now, return null (unknown)
        // Known providers return high age
        if ($this->isFreeEmailProvider($domain)) {
            return 5000; // 5000+ days (very old)
        }

        return null; // Unknown
    }

    /**
     * Calculate email risk score
     */
    protected function calculateEmailRisk(string $email, string $domain, bool $isDisposable, bool $isFreeProvider, ?int $domainAge): int
    {
        $score = 0;

        // Disposable email = very high risk
        if ($isDisposable) {
            $score += 80;
        }

        // Free provider = slightly elevated risk
        if ($isFreeProvider && !$isDisposable) {
            $score += 10;
        }

        // New domain = higher risk
        if ($domainAge !== null && $domainAge < 30) {
            $score += 40;
        } elseif ($domainAge !== null && $domainAge < 90) {
            $score += 20;
        }

        // Very short local part = suspicious
        $localPart = substr($email, 0, strpos($email, '@'));
        if (strlen($localPart) < 3) {
            $score += 15;
        }

        // Contains suspicious patterns
        $suspiciousPatterns = ['test', 'fake', 'spam', 'temp', 'disposable', '123', '000'];
        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($localPart, $pattern) !== false) {
                $score += 10;
                break;
            }
        }

        // All numbers in local part
        if (preg_match('/^[0-9]+$/', $localPart)) {
            $score += 20;
        }

        return min($score, 100);
    }

    /**
     * Get email warnings
     */
    protected function getEmailWarnings(bool $isDisposable, bool $isFreeProvider, ?int $domainAge): array
    {
        $warnings = [];

        if ($isDisposable) {
            $warnings[] = 'Disposable/temporary email address';
        }

        if ($isFreeProvider) {
            $warnings[] = 'Free email provider';
        }

        if ($domainAge !== null && $domainAge < 30) {
            $warnings[] = 'Very new domain (< 30 days)';
        }

        return $warnings;
    }

    /**
     * Validate and score phone number
     *
     * @param string $phone
     * @return array Phone intelligence data
     */
    public function validatePhone(string $phone): array
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        // Basic validation
        $digitCount = strlen(preg_replace('/[^0-9]/', '', $cleaned));

        if ($digitCount < 10 || $digitCount > 15) {
            return [
                'is_valid' => false,
                'risk_score' => 80,
                'reason' => 'Invalid phone number length',
            ];
        }

        // Extract country code
        $countryCode = $this->extractCountryCode($cleaned);

        // Perform checks
        $isMobile = $this->isMobileNumber($cleaned);
        $isVoip = $this->isVoipNumber($cleaned);
        $isLandline = !$isMobile && !$isVoip;

        // Calculate risk score
        $riskScore = $this->calculatePhoneRisk($cleaned, $isMobile, $isVoip, $countryCode);

        return [
            'is_valid' => true,
            'phone' => $cleaned,
            'country_code' => $countryCode,
            'is_mobile' => $isMobile,
            'is_landline' => $isLandline,
            'is_voip' => $isVoip,
            'risk_score' => $riskScore,
            'risk_level' => $this->getRiskLevel($riskScore),
            'warnings' => $this->getPhoneWarnings($isVoip, $countryCode),
        ];
    }

    /**
     * Extract country code from phone number
     */
    protected function extractCountryCode(string $phone): string
    {
        // Tanzania: +255
        if (preg_match('/^(\+?255|0)/', $phone)) {
            return '+255';
        }

        // Kenya: +254
        if (preg_match('/^(\+?254)/', $phone)) {
            return '+254';
        }

        // Uganda: +256
        if (preg_match('/^(\+?256)/', $phone)) {
            return '+256';
        }

        // Rwanda: +250
        if (preg_match('/^(\+?250)/', $phone)) {
            return '+250';
        }

        // Default
        return 'Unknown';
    }

    /**
     * Check if phone number is mobile
     */
    protected function isMobileNumber(string $phone): bool
    {
        // Tanzania mobile prefixes
        // Format: +255 6XX XXX XXX or +255 7XX XXX XXX
        $mobilePrefixes = [
            '2556', // Vodacom, Tigo, Airtel
            '2557', // Vodacom, Tigo, Airtel, Halotel
        ];

        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        foreach ($mobilePrefixes as $prefix) {
            if (strpos($cleaned, $prefix) === 0) {
                return true;
            }
        }

        // Also check for local format starting with 0
        if (preg_match('/^0[67]/', $cleaned)) {
            return true;
        }

        return false;
    }

    /**
     * Check if phone number is VoIP
     */
    protected function isVoipNumber(string $phone): bool
    {
        // VoIP detection would require external API (e.g., Twilio Lookup, NumVerify)
        // Basic check: certain area codes or patterns

        // Known VoIP prefixes (example - update with actual data)
        $voipPrefixes = [
            // Add known VoIP prefixes
        ];

        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        foreach ($voipPrefixes as $prefix) {
            if (strpos($cleaned, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate phone risk score
     */
    protected function calculatePhoneRisk(string $phone, bool $isMobile, bool $isVoip, string $countryCode): int
    {
        $score = 0;

        // VoIP = higher risk
        if ($isVoip) {
            $score += 50;
        }

        // Landline for financial transactions = slightly elevated
        if (!$isMobile && !$isVoip) {
            $score += 15;
        }

        // Foreign country code (if not expected)
        // Assuming Tanzania is primary market
        if ($countryCode !== '+255' && $countryCode !== 'Unknown') {
            $score += 20;
        }

        // Unknown country code
        if ($countryCode === 'Unknown') {
            $score += 30;
        }

        return min($score, 100);
    }

    /**
     * Get phone warnings
     */
    protected function getPhoneWarnings(bool $isVoip, string $countryCode): array
    {
        $warnings = [];

        if ($isVoip) {
            $warnings[] = 'VoIP phone number detected';
        }

        if ($countryCode !== '+255' && $countryCode !== 'Unknown') {
            $warnings[] = 'Foreign country code: ' . $countryCode;
        }

        if ($countryCode === 'Unknown') {
            $warnings[] = 'Unknown country code';
        }

        return $warnings;
    }

    /**
     * Get risk level from score
     */
    protected function getRiskLevel(int $score): string
    {
        if ($score >= 80) return 'critical';
        if ($score >= 60) return 'high';
        if ($score >= 40) return 'medium';
        if ($score >= 20) return 'low';
        return 'minimal';
    }

    /**
     * Validate both email and phone together
     */
    public function validateContact(string $email, string $phone): array
    {
        $emailData = $this->validateEmail($email);
        $phoneData = $this->validatePhone($phone);

        // Combined risk score (weighted average)
        $combinedRisk = ($emailData['risk_score'] * 0.6) + ($phoneData['risk_score'] * 0.4);

        return [
            'email' => $emailData,
            'phone' => $phoneData,
            'combined_risk_score' => round($combinedRisk),
            'combined_risk_level' => $this->getRiskLevel(round($combinedRisk)),
            'recommendation' => $this->getRecommendation($combinedRisk),
        ];
    }

    /**
     * Get recommendation based on risk
     */
    protected function getRecommendation(float $riskScore): string
    {
        if ($riskScore >= 80) {
            return 'Block transaction - high fraud risk';
        }

        if ($riskScore >= 60) {
            return 'Require additional verification';
        }

        if ($riskScore >= 40) {
            return 'Monitor closely';
        }

        return 'Proceed normally';
    }
}

<?php

namespace App\Livewire;

use Livewire\Component;

class AuthSimulator extends Component
{
    public string $activeTab = 'signin'; // 'signin', 'signup', 'phishing'
    
    // Sign-in flow state
    public string $email = 'alex.chen@enterprise.dev';
    public string $step = 'idle'; // 'idle', 'initiating', 'awaiting_approval', 'approved', 'rejected'
    public string $challengeCode = '842-195';
    public string $clientLocation = 'San Francisco, CA • Chrome 128 (macOS)';
    public string $clientIp = '192.0.2.14';
    public int $authLatencyMs = 420;

    // Pairing flow state
    public string $pairingStep = 'qr'; // 'qr', 'scanning', 'paired'
    public string $pairingNonce = 'SEC-8921-XQ';

    // Phishing simulation state
    public string $threatStep = 'idle'; // 'idle', 'alerting', 'blocked'
    public string $threatLocation = 'Unknown Proxy • Firefox 115 (Linux)';
    public string $threatIp = '198.51.100.77';

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetFlows();
    }

    public function resetFlows(): void
    {
        $this->step = 'idle';
        $this->pairingStep = 'qr';
        $this->threatStep = 'idle';
        $this->challengeCode = str_pad((string) rand(100, 999), 3, '0', STR_PAD_LEFT) . '-' . str_pad((string) rand(100, 999), 3, '0', STR_PAD_LEFT);
    }

    public function initiateSignIn(): void
    {
        $this->step = 'initiating';
        $this->challengeCode = str_pad((string) rand(100, 999), 3, '0', STR_PAD_LEFT) . '-' . str_pad((string) rand(100, 999), 3, '0', STR_PAD_LEFT);
        $this->authLatencyMs = rand(380, 540);
        $this->step = 'awaiting_approval';
    }

    public function approveOnPhone(): void
    {
        $this->step = 'approved';
    }

    public function rejectOnPhone(): void
    {
        $this->step = 'rejected';
    }

    public function scanPairingQr(): void
    {
        $this->pairingStep = 'scanning';
        $this->pairingStep = 'paired';
    }

    public function triggerPhishingAttack(): void
    {
        $this->threatStep = 'alerting';
    }

    public function blockThreatOnPhone(): void
    {
        $this->threatStep = 'blocked';
    }

    public function render()
    {
        return view('livewire.auth-simulator');
    }
}

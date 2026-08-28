<?php

namespace App\Livewire;

use App\Models\EarlyAccessSubscriber;
use Livewire\Component;

class WaitlistForm extends Component
{
    public string $email = '';
    public string $role = 'developer';
    public string $teamSize = '1-10';
    public string $useCase = '';
    public bool $submitted = false;
    public int $queuePosition = 142;
    public string $referralCode = '';

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255', 'unique:early_access_subscribers,email'],
            'role' => ['required', 'string', 'in:developer,security_engineer,founder_cto,enterprise_it,product_manager'],
            'teamSize' => ['required', 'string', 'in:1-10,11-50,51-200,200+'],
            'useCase' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected $messages = [
        'email.required' => 'Please enter your work or personal email address.',
        'email.email' => 'Please provide a valid email format (e.g. name@company.com).',
        'email.unique' => "You're already on our VIP early access list!",
    ];

    public function submit(): void
    {
        $this->validate();

        EarlyAccessSubscriber::create([
            'email' => strtolower(trim($this->email)),
            'role' => $this->role,
            'team_size' => $this->teamSize,
            'use_case' => $this->useCase ?: 'Dual-device sign-in security evaluation',
            'ip_address' => request()->ip() ?? '127.0.0.1',
        ]);

        $this->queuePosition = rand(135, 189);
        $this->referralCode = 'SEC-' . strtoupper(substr(md5($this->email), 0, 6));
        $this->submitted = true;
    }

    public function resetForm(): void
    {
        $this->email = '';
        $this->useCase = '';
        $this->submitted = false;
    }

    public function render()
    {
        return view('livewire.waitlist-form');
    }
}

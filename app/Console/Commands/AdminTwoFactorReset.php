<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;

/**
 * For an admin who lost the phone and the recovery codes: turns the second factor off from the server. The password still
 * has to be known; if it is lost too, set a new one with tinker (Admin::where('username', ...)->first()->update(['password' => ...])).
 */
class AdminTwoFactorReset extends Command
{
    protected $signature = 'admin:2fa-reset {username : the username of the admin}';

    protected $description = 'Turn off the second factor (TOTP) of an admin account';

    public function handle(): int
    {
        $admin = Admin::where('username', $this->argument('username'))->first();
        if (!$admin) {
            $this->error('No admin with that username.');

            return self::FAILURE;
        }

        if (!$admin->hasTwoFactorEnabled() && $admin->two_factor_secret === null) {
            $this->info("{$admin->username} has no second factor.");

            return self::SUCCESS;
        }

        $admin->clearTwoFactor();
        $this->info("The second factor of {$admin->username} is off. Sign in with the password and turn it on again at the Security page.");

        return self::SUCCESS;
    }
}

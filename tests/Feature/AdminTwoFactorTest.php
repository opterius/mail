<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Services\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'correct horse battery';

    private function admin(): Admin
    {
        return Admin::create(['name' => 'Boss', 'username' => 'boss', 'password' => self::PASSWORD]);
    }

    /** An admin with the second factor on: returns [admin, secret, recovery codes]. */
    private function adminWithTwoFactor(): array
    {
        $admin  = $this->admin();
        $totp   = new TotpService();
        $secret = $totp->generateSecret();
        $codes  = $totp->generateRecoveryCodes();
        $admin->forceFill([
            'two_factor_secret'         => $secret,
            'two_factor_recovery_codes' => json_encode($codes),
            'two_factor_enabled_at'     => now(),
        ])->save();

        return [$admin, $secret, $codes];
    }

    private function signIn(string $password = self::PASSWORD)
    {
        return $this->post(route('admin.login.submit'), ['username' => 'boss', 'password' => $password]);
    }

    /** A 6 digit code that is not valid now (nor one window before or after). */
    private function wrongCode(string $secret): string
    {
        $totp  = new TotpService();
        $valid = [$totp->code($secret, time() - 30), $totp->code($secret), $totp->code($secret, time() + 30)];
        foreach (['000000', '111111', '222222'] as $c) {
            if (!in_array($c, $valid, true)) {
                return $c;
            }
        }
        $this->fail('no wrong code found');
    }

    // ── the code itself ──

    public function test_the_code_matches_the_rfc_6238_test_vector(): void
    {
        // RFC 6238, appendix B: the secret "12345678901234567890" at 59 seconds gives 94287082 (8 digits): 287082 in 6
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
        $this->assertSame('287082', (new TotpService())->code($secret, 59));
        $this->assertTrue((new TotpService())->verify($secret, (new TotpService())->code($secret)));
    }

    // ── signing in ──

    public function test_without_the_second_factor_the_password_signs_in(): void
    {
        $this->admin();

        $this->signIn()->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated('admin');
    }

    public function test_a_wrong_password_is_refused_the_same_way_with_or_without_the_second_factor(): void
    {
        $this->adminWithTwoFactor();

        $this->from(route('admin.login'))->signIn('wrong password')
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('username');
        $this->assertGuest('admin');
    }

    public function test_with_the_second_factor_the_password_alone_does_not_sign_in(): void
    {
        $this->adminWithTwoFactor();

        $this->signIn()->assertRedirect(route('admin.2fa'));
        $this->assertGuest('admin');
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_a_right_code_signs_in(): void
    {
        [, $secret] = $this->adminWithTwoFactor();

        $this->signIn();
        $this->post(route('admin.2fa.verify'), ['code' => (new TotpService())->code($secret)])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated('admin');
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_a_wrong_code_does_not_sign_in(): void
    {
        [, $secret] = $this->adminWithTwoFactor();

        $this->signIn();
        $this->post(route('admin.2fa.verify'), ['code' => $this->wrongCode($secret)])
            ->assertSessionHasErrors('code');

        $this->assertGuest('admin');
    }

    public function test_five_wrong_codes_send_the_person_back_to_the_password(): void
    {
        [, $secret] = $this->adminWithTwoFactor();
        $wrong = $this->wrongCode($secret);

        $this->signIn();
        for ($i = 0; $i < 4; $i++) {
            $this->post(route('admin.2fa.verify'), ['code' => $wrong])->assertSessionHasErrors('code');
        }
        $this->post(route('admin.2fa.verify'), ['code' => $wrong])->assertRedirect(route('admin.login'));

        // the sign-in that was waiting is gone: even the right code does not help now
        $this->post(route('admin.2fa.verify'), ['code' => (new TotpService())->code($secret)])
            ->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_the_code_page_and_the_code_need_a_right_password_first(): void
    {
        [, $secret] = $this->adminWithTwoFactor();

        $this->get(route('admin.2fa'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.2fa.verify'), ['code' => (new TotpService())->code($secret)])
            ->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_the_sign_in_that_waits_for_its_code_expires(): void
    {
        [, $secret] = $this->adminWithTwoFactor();

        $this->signIn();
        $this->travel(6)->minutes();
        $this->post(route('admin.2fa.verify'), ['code' => (new TotpService())->code($secret)])
            ->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_a_recovery_code_works_once(): void
    {
        [, , $codes] = $this->adminWithTwoFactor();

        $this->signIn();
        $this->post(route('admin.2fa.verify'), ['code' => $codes[0]])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated('admin');

        $this->post(route('admin.logout'));
        $this->assertGuest('admin');

        $this->signIn();
        $this->post(route('admin.2fa.verify'), ['code' => $codes[0]])->assertSessionHasErrors('code');
        $this->assertGuest('admin');
        $this->post(route('admin.2fa.verify'), ['code' => strtolower($codes[1])])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated('admin');
    }

    // ── turning it on and off ──

    public function test_the_security_page_needs_a_sign_in(): void
    {
        $this->admin();
        $this->get(route('admin.security.index'))->assertRedirect(route('admin.login'));
    }

    public function test_turning_it_on_needs_a_code_from_the_app(): void
    {
        $admin = $this->admin();
        $this->signIn();

        $page = $this->get(route('admin.security.index'))->assertOk();
        $admin->refresh();
        $secret = $admin->two_factor_secret;
        $this->assertNotNull($secret);
        $page->assertSee($secret);
        $this->assertFalse($admin->hasTwoFactorEnabled(), 'a secret that was only shown does not count yet');

        // the secret that was shown stays the same when the page is opened again
        $this->get(route('admin.security.index'));
        $this->assertSame($secret, $admin->fresh()->two_factor_secret);

        $this->post(route('admin.security.2fa.enable'), ['code' => $this->wrongCode($secret)])->assertSessionHasErrors('code');
        $this->assertFalse($admin->fresh()->hasTwoFactorEnabled());

        $this->post(route('admin.security.2fa.enable'), ['code' => (new TotpService())->code($secret)])
            ->assertRedirect(route('admin.security.index'))
            ->assertSessionHas('recovery_codes_fresh');

        $admin->refresh();
        $this->assertTrue($admin->hasTwoFactorEnabled());
        $this->assertCount(10, $admin->recoveryCodes());
        $this->assertSame(10, count(session('recovery_codes_fresh')));
    }

    public function test_the_secret_and_the_recovery_codes_are_encrypted_in_the_database(): void
    {
        [$admin, $secret, $codes] = $this->adminWithTwoFactor();

        $row = DB::table('admins')->where('id', $admin->id)->first();
        $this->assertNotSame($secret, $row->two_factor_secret);
        $this->assertStringNotContainsString($secret, $row->two_factor_secret);
        $this->assertStringNotContainsString($codes[0], $row->two_factor_recovery_codes);
        $this->assertSame($secret, $admin->fresh()->two_factor_secret);
    }

    public function test_turning_it_off_needs_the_password_and_a_code(): void
    {
        [$admin, $secret] = $this->adminWithTwoFactor();
        $this->signIn();
        $this->post(route('admin.2fa.verify'), ['code' => (new TotpService())->code($secret)]);
        $this->assertAuthenticated('admin');

        $good = (new TotpService())->code($secret);
        $this->post(route('admin.security.2fa.disable'), ['password' => 'wrong password', 'code' => $good])
            ->assertSessionHasErrors('password');
        $this->assertTrue($admin->fresh()->hasTwoFactorEnabled());

        $this->post(route('admin.security.2fa.disable'), ['password' => self::PASSWORD, 'code' => $this->wrongCode($secret)])
            ->assertSessionHasErrors('code');
        $this->assertTrue($admin->fresh()->hasTwoFactorEnabled());

        $this->post(route('admin.security.2fa.disable'), ['password' => self::PASSWORD, 'code' => $good])
            ->assertRedirect(route('admin.security.index'));
        $admin->refresh();
        $this->assertFalse($admin->hasTwoFactorEnabled());
        $this->assertNull($admin->two_factor_secret);
        $this->assertNull($admin->two_factor_recovery_codes);
    }

    // ── from the server ──

    public function test_the_reset_command_turns_it_off(): void
    {
        [$admin] = $this->adminWithTwoFactor();

        $this->artisan('admin:2fa-reset', ['username' => 'boss'])->assertExitCode(0);
        $this->assertFalse($admin->fresh()->hasTwoFactorEnabled());
        $this->assertNull($admin->fresh()->two_factor_secret);

        // and the password alone signs in again
        $this->signIn()->assertRedirect(route('admin.dashboard'));
    }

    public function test_the_reset_command_refuses_an_unknown_username(): void
    {
        $this->admin();
        $this->artisan('admin:2fa-reset', ['username' => 'nobody'])->assertExitCode(1);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('role')->default('user');
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('user_name');
                $table->string('action');
                $table->string('module')->nullable();
                $table->string('target')->nullable();
                $table->text('details')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }
    }
    /**
     * Test 1: Security headers are present in web responses.
     */
    public function test_security_headers_are_present_in_responses(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(self), geolocation=(self), microphone=()');
    }

    /**
     * Test 2: Input sanitization strips HTML tags from user inputs.
     */
    public function test_input_sanitization_strips_html_tags(): void
    {
        $middleware = new \App\Http\Middleware\SanitizeInput();
        $request = \Illuminate\Http\Request::create('/test', 'POST', [
            'name' => '<script>alert("XSS")</script>Ahmed',
            'reason' => '<b>Bold Text</b> & regular text',
            'password' => '<KeepOriginalPassword123!>',
        ]);

        $middleware->handle($request, function ($req) {
            $this->assertEquals('alert("XSS")Ahmed', $req->input('name'));
            $this->assertEquals('Bold Text & regular text', $req->input('reason'));
            $this->assertEquals('<KeepOriginalPassword123!>', $req->input('password'));
            return new \Illuminate\Http\Response();
        });
    }

    /**
     * Test 3: Login rate limiting blocks brute-force attacks after 5 attempts.
     */
    public function test_login_rate_limiting_triggers_after_repeated_failures(): void
    {
        RateLimiter::clear('attacker@example.com|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'attacker@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // 6th attempt should be blocked by Rate Limiter
        $response = $this->post('/login', [
            'email' => 'attacker@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $errors = session('errors')->get('email');
        $this->assertStringContainsString('تم حظر محاولات الدخول مؤقتاً', $errors[0]);

        RateLimiter::clear('attacker@example.com|127.0.0.1');
    }

    /**
     * Test 4: Verify directory listing is disabled in .htaccess.
     */
    public function test_htaccess_disables_directory_listing(): void
    {
        $publicHtaccess = file_get_contents(public_path('.htaccess'));
        $this->assertStringContainsString('Options -Indexes', $publicHtaccess);

        $rootHtaccess = file_get_contents(base_path('.htaccess'));
        $this->assertStringContainsString('Options -Indexes', $rootHtaccess);
    }
}

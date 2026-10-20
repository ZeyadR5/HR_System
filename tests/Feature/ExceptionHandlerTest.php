<?php

use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware('web')->get('/test-error-get', function () {
        throw new \Exception('Testing simulated error');
    });

    Route::middleware('web')->post('/test-error-post', function () {
        throw new \Exception('Testing simulated POST error');
    });

    Route::get('/test-error-json', function () {
        throw new \Exception('Testing simulated JSON error');
    });
});

test('it renders friendly 500 view on GET exception and does not expose trace', function () {
    $response = $this->get('/test-error-get');

    $response->assertStatus(500);
    $response->assertSee('عذراً، حدث خطأ غير متوقع!');
    $response->assertDontSee('Testing simulated error');
    $response->assertDontSee('Stack trace');
});

test('it returns friendly json on API/json exception', function () {
    $response = $this->getJson('/test-error-json');

    $response->assertStatus(500);
    $response->assertJson([
        'success' => false,
        'message' => 'حدث خطأ غير متوقع أثناء تنفيذ العملية، تم تسجيل الخطأ للمتابعة.',
    ]);
    $response->assertDontSee('Testing simulated JSON error');
});

test('it redirects back on POST form exception with friendly error', function () {
    $response = $this->from('/previous-page')->post('/test-error-post', [
        'some_field' => 'value',
    ]);

    $response->assertRedirect('/previous-page');
    $response->assertSessionHasErrors(['error']);
});

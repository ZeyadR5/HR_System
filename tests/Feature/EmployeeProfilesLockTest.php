<?php

use App\Http\Middleware\LockEmployeeProfilesOnExit;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

test('it forgets unlocked_employee_profiles when navigating to outside routes on GET', function () {
    $middleware = new LockEmployeeProfilesOnExit();

    $request = Request::create('/leaves', 'GET');
    $session = app('session.store');
    $session->put('unlocked_employee_profiles', true);
    $request->setLaravelSession($session);

    $response = $middleware->handle($request, function ($req) {
        return new Response();
    });

    expect($request->session()->has('unlocked_employee_profiles'))->toBeFalse();
});

test('it preserves unlocked_employee_profiles when navigating to employee-profiles on GET', function () {
    $middleware = new LockEmployeeProfilesOnExit();

    $request = Request::create('/employee-profiles', 'GET');
    $session = app('session.store');
    $session->put('unlocked_employee_profiles', true);
    $request->setLaravelSession($session);

    $response = $middleware->handle($request, function ($req) {
        return new Response();
    });

    expect($request->session()->has('unlocked_employee_profiles'))->toBeTrue();
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});

test('it preserves unlocked_employee_profiles when navigating inside employee-profiles subroutes', function () {
    $middleware = new LockEmployeeProfilesOnExit();

    $request = Request::create('/employee-profiles/12/edit', 'GET');
    $session = app('session.store');
    $session->put('unlocked_employee_profiles', true);
    $request->setLaravelSession($session);

    $response = $middleware->handle($request, function ($req) {
        return new Response();
    });

    expect($request->session()->has('unlocked_employee_profiles'))->toBeTrue();
});

test('it does not clear unlocked_employee_profiles for json background requests', function () {
    $middleware = new LockEmployeeProfilesOnExit();

    $request = Request::create('/poll-notifications', 'POST', [], [], [], [
        'HTTP_ACCEPT' => 'application/json',
    ]);
    $session = app('session.store');
    $session->put('unlocked_employee_profiles', true);
    $request->setLaravelSession($session);

    $response = $middleware->handle($request, function ($req) {
        return new Response();
    });

    expect($request->session()->has('unlocked_employee_profiles'))->toBeTrue();
});

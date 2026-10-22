<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\view_Overtime;
use App\Http\Controllers\PenaltyController;
use App\Http\Controllers\SuperAdminEntryController;
use App\Http\Controllers\LeavePermissionController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\CheckInOutController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminNoteController;
use App\Http\Controllers\IncentiveController;
use App\Http\Controllers\InstructionController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\DeveloperSettingsController;
use App\Http\Controllers\DeveloperShiftGroupController;
use App\Http\Controllers\ShiftRosterController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('login', [LoginController::class, 'login'])->name('login');
Route::post('login', [LoginController::class, 'login_post'])->name('login_post');

Route::post('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('logout', [LoginController::class, 'logout']);


// ==========================================
// يوزر + ادمن + سوبر ادمن (كل المسجلين)
// ==========================================


Route::middleware(['auth'])->group(function () {

    // اضافي
    Route::get('/overtime', [OvertimeController::class, 'overtime'])->name('Overtime');
    Route::post('/overtime_post', [OvertimeController::class, 'overtime_post'])->name('overtime_post');
    Route::get('/view_overtime', [view_Overtime::class, 'view_Overtime'])->name('view_Overtime');

    // اجازات
    Route::get('/leave', [LeavePermissionController::class, 'leave_index'])->name('leave');
    Route::post('/leave', [LeavePermissionController::class, 'leave_post'])->name('leave_post');
    Route::get('/view_leave', [LeavePermissionController::class, 'view_leave'])->name('view_leave');

    // اذونات
    Route::get('/permission', [LeavePermissionController::class, 'permission_index'])->name('permission');
    Route::post('/permission', [LeavePermissionController::class, 'permission_post'])->name('permission_post');
    Route::get('/view_permission', [LeavePermissionController::class, 'view_permission'])->name('view_permission');

    // حضور وانصراف يدوي
    Route::get('/check-in-out', [CheckInOutController::class, 'index_front'])->name('check_in_out');
    Route::post('/check-in-out', [CheckInOutController::class, 'store'])->name('check_in_out_post');
    Route::get('/view-check-in-out', [CheckInOutController::class, 'index_view'])->name('view_check_in_out');

    // جزاءات - عرض (للكل)
    Route::get('/my-penalties', [PenaltyController::class, 'view_penalties'])->name('view_penalties');

    // تسويات - للموظف والكل
    Route::get('/settlement', [SettlementController::class, 'create'])->name('settlements.create');
    Route::post('/settlement', [SettlementController::class, 'store'])->name('settlements.store');
    Route::get('/view_settlements', [SettlementController::class, 'index'])->name('settlements.index');

    // 📅 جدول الشيفتات وتوزيع الشيفتات
    Route::prefix('rosters')->name('rosters.')->group(function () {
        Route::get('/', [ShiftRosterController::class, 'index'])->name('index');
        Route::get('/create', [ShiftRosterController::class, 'create'])->name('create');
        Route::post('/store', [ShiftRosterController::class, 'store'])->name('store');
        Route::get('/template/download', [ShiftRosterController::class, 'downloadTemplate'])->name('template.download');
        Route::get('/{roster}', [ShiftRosterController::class, 'show'])->name('show');
        Route::get('/{roster}/edit', [ShiftRosterController::class, 'edit'])->name('edit');
        Route::post('/{roster}/update', [ShiftRosterController::class, 'update'])->name('update');
        Route::post('/{roster}/update-cell', [ShiftRosterController::class, 'updateCell'])->name('update_cell');
        Route::get('/{roster}/export', [ShiftRosterController::class, 'export'])->name('export');
        Route::post('/{roster}/delete', [ShiftRosterController::class, 'destroy'])->name('destroy');
    });

    // بروفايلي الشخصي
    Route::get('/my-profile', [EmployeeProfileController::class, 'myProfile'])->name('my_profile');
    Route::post('/my-profile/update-picture', [EmployeeProfileController::class, 'updateMyProfilePicture'])->name('my_profile.update_picture');

    // صفحة الإشعارات
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // جلب الإشعارات غير المقروءة للموبايل
    Route::post('/poll-notifications', function (\Illuminate\Http\Request $request) {
        if (auth()->check()) {
            $user = auth()->user();
            $notifications = $user->unreadNotifications;
            
            // تحويل الإشعارات لمصفوفة بسيطة
            $data = $notifications->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->data['title'] ?? 'إشعار جديد',
                    'message' => $notif->data['message'] ?? '',
                ];
            });

            // تعليم الإشعارات كمقروءة فور جلبها للموبايل
            if ($notifications->count() > 0) {
                $user->unreadNotifications->markAsRead();
            }

            return response()->json(['status' => 'success', 'notifications' => $data]);
        }
        return response()->json(['status' => 'error'], 400);
    })->name('poll_notifications');
});


// ==========================================
// المسارات الإدارية — كل مسار محمي بصلاحية محددة
// (الصلاحيات بتتحدد لكل فئة من صفحة المطور: إدارة الفئات والصلاحيات)
// ==========================================
Route::middleware(['auth'])->group(function () {

    // ---------- الإضافي ----------
    Route::post('/overtime/{id}/accept', [OvertimeController::class, 'accept'])->middleware('permission:overtime.approve')->name('overtime.accept');
    Route::post('/overtime/{id}/refuse', [OvertimeController::class, 'refuse'])->middleware('permission:overtime.approve')->name('overtime.refuse');
    Route::get('/overtime/export', [OvertimeController::class, 'export'])->middleware('permission:overtime.export')->name('overtime.export');

    // ---------- الإجازات ----------
    Route::post('/leave/{id}/accept', [LeavePermissionController::class, 'leave_accept'])->middleware('permission:leave.approve')->name('leave.accept');
    Route::post('/leave/{id}/refuse', [LeavePermissionController::class, 'leave_refuse'])->middleware('permission:leave.approve')->name('leave.refuse');
    Route::get('/leaves/export', [LeavePermissionController::class, 'export_lev'])->middleware('permission:leave.export')->name('leave.export');

    // ---------- الأذونات ----------
    Route::post('/permission/{id}/accept', [LeavePermissionController::class, 'permission_accept'])->middleware('permission:permission.approve')->name('permission.accept');
    Route::post('/permission/{id}/refuse', [LeavePermissionController::class, 'permission_refuse'])->middleware('permission:permission.approve')->name('permission.refuse');
    Route::get('/permissions/export', [LeavePermissionController::class, 'export_per'])->middleware('permission:permission.export')->name('permission.export');

    // ---------- الحضور والانصراف ----------
    Route::post('/check-in-out/{id}/accept', [CheckInOutController::class, 'accept'])->middleware('permission:checkinout.approve')->name('check_in_out.accept');
    Route::post('/check-in-out/{id}/refuse', [CheckInOutController::class, 'refuse'])->middleware('permission:checkinout.approve')->name('check_in_out.refuse');
    Route::get('/check-in-out/export', [CheckInOutController::class, 'export'])->middleware('permission:checkinout.export')->name('check_in_out.export');

    // ---------- الجزاءات ----------
    Route::get('/admin/penalties', [PenaltyController::class, 'index'])->middleware('permission:penalties.create')->name('admin.penalties');
    Route::post('/admin/penalties', [PenaltyController::class, 'store'])->middleware('permission:penalties.create')->name('admin.penalties.store');
    Route::post('/penalties/{id}/accept', [PenaltyController::class, 'accept'])->middleware('permission:penalties.approve')->name('penalties.accept');
    Route::post('/penalties/{id}/refuse', [PenaltyController::class, 'refuse'])->middleware('permission:penalties.approve')->name('penalties.refuse');
    Route::get('/penalties/export', [PenaltyController::class, 'export'])->middleware('permission:penalties.export')->name('penalties.export');

    // ---------- التسويات ----------
    Route::post('/settlements/{id}/accept', [SettlementController::class, 'accept'])->middleware('permission:settlements.approve')->name('settlements.accept');
    Route::post('/settlements/{id}/refuse', [SettlementController::class, 'refuse'])->middleware('permission:settlements.approve')->name('settlements.refuse');
    Route::get('/settlements/export', [SettlementController::class, 'export'])->middleware('permission:settlements.export')->name('settlements.export');

    // ---------- الموظفين ----------
    Route::get('/admin/reset-password', [LoginController::class, 'reset_password_index'])->middleware('permission:employees.reset_password')->name('admin.reset_password');
    Route::post('/admin/reset-password', [LoginController::class, 'reset_password_post'])->middleware('permission:employees.reset_password')->name('admin.reset_password.post');

    Route::get('/super-admin/entry', [SuperAdminEntryController::class, 'index'])->middleware('permission:employees.manual_entry')->name('super_admin.entry');
    Route::post('/super-admin/entry', [SuperAdminEntryController::class, 'store'])->middleware('permission:employees.manual_entry')->name('super_admin.entry.store');

    Route::get('/employee-profiles', [EmployeeProfileController::class, 'index'])->middleware('permission:employees.view_profiles')->name('employee_profiles.index');
    Route::get('/employee-profiles/archive', [EmployeeProfileController::class, 'archive'])->middleware('permission:employees.view_profiles')->name('employee_profiles.archive');
    Route::post('/employee-profiles/{id}/restore', [EmployeeProfileController::class, 'restore'])->middleware('permission:employees.edit_profiles')->name('employee_profiles.restore');
    Route::post('/employee-profiles/unlock-module', [EmployeeProfileController::class, 'unlockModule'])->middleware('permission:employees.view_profiles')->name('employee_profiles.unlock_module');
    Route::post('/employee-profiles/lock-module', [EmployeeProfileController::class, 'lockModule'])->name('employee_profiles.lock_module');
    Route::get('/employee-profiles/create', [EmployeeProfileController::class, 'create'])->middleware('permission:employees.edit_profiles')->name('employee_profiles.create');
    Route::post('/employee-profiles/check-email', [EmployeeProfileController::class, 'checkEmail'])->name('employee_profiles.check_email');
    Route::post('/employee-profiles/store', [EmployeeProfileController::class, 'store'])->middleware('permission:employees.edit_profiles')->name('employee_profiles.store');
    Route::get('/employee-profiles/{id}', [EmployeeProfileController::class, 'show'])->middleware('permission:employees.view_profiles')->name('employee_profiles.show');
    Route::post('/employee-profiles/{id}/unlock-show', [EmployeeProfileController::class, 'unlockShow'])->middleware('permission:employees.view_profiles')->name('employee_profiles.unlock_show');
    Route::post('/employee-profiles/{id}/unlock-edit', [EmployeeProfileController::class, 'unlockEdit'])->middleware('permission:employees.edit_profiles')->name('employee_profiles.unlock_edit');
    Route::get('/employee-profiles/{id}/edit', [EmployeeProfileController::class, 'edit'])->middleware('permission:employees.edit_profiles')->name('employee_profiles.edit');
    Route::post('/employee-profiles/{id}/update', [EmployeeProfileController::class, 'update'])->middleware('permission:employees.edit_profiles')->name('employee_profiles.update');
    Route::post('/employee-profiles/{id}/delete', [EmployeeProfileController::class, 'destroy'])->middleware('permission:employees.edit_profiles')->name('employee_profiles.destroy');
    Route::get('/employee-profiles/{id}/document/{type}', [EmployeeProfileController::class, 'viewDocument'])->name('employee_profiles.document');
    Route::post('/employee-profiles/{id}/upload-document-ajax', [EmployeeProfileController::class, 'uploadDocumentAjax'])->name('employee_profiles.upload_document_ajax');
    Route::post('/employee-profiles/{id}/delete-document-ajax', [EmployeeProfileController::class, 'deleteDocumentAjax'])->name('employee_profiles.delete_document_ajax');
    Route::post('/employee-profiles/{id}/toggle-insurance', [EmployeeProfileController::class, 'toggleInsuranceAjax'])->middleware('permission:employees.edit_profiles')->name('employee_profiles.toggle_insurance');

    // ---------- التقارير والسجلات ----------
    Route::get('/audit-log', [AuditController::class, 'index'])->middleware('permission:reports.audit_log')->name('audit_log');
    Route::get('/full-report', [ReportController::class, 'index'])->middleware('permission:reports.full_report')->name('full_report.index');
    Route::get('/full-report/export', [ReportController::class, 'export'])->middleware('permission:reports.full_report')->name('full_report.export');

    // ---------- ملاحظات الإدارة ----------
    Route::get('/admin-notes/create', [AdminNoteController::class, 'create'])->middleware('permission:admin_notes.create')->name('admin_notes.create');
    Route::post('/admin-notes/store', [AdminNoteController::class, 'store'])->middleware('permission:admin_notes.create')->name('admin_notes.store');
    Route::get('/admin-notes', [AdminNoteController::class, 'index'])->middleware('permission:admin_notes.view')->name('admin_notes.index');

    // ---------- التعليمات والتنبيهات ----------
    Route::get('/instructions', [InstructionController::class, 'index'])->name('instructions.index');
    Route::post('/instructions', [InstructionController::class, 'store'])->name('instructions.store');
    Route::post('/instructions/{instruction}/pin', [InstructionController::class, 'togglePin'])->name('instructions.pin');
    Route::post('/instructions/{instruction}/delete', [InstructionController::class, 'destroy'])->name('instructions.destroy');
    Route::get('/admin-notes/export', [AdminNoteController::class, 'export'])->middleware('permission:admin_notes.export')->name('admin_notes.export');

    // ---------- الحوافز ----------
    Route::get('/incentives/create', [IncentiveController::class, 'create'])->middleware('permission:incentives.create')->name('incentives.create');
    Route::post('/incentives/store', [IncentiveController::class, 'store'])->middleware('permission:incentives.create')->name('incentives.store');
    Route::get('/incentives', [IncentiveController::class, 'index'])->middleware('permission:incentives.view')->name('incentives.index');
    Route::get('/incentives/export', [IncentiveController::class, 'export'])->middleware('permission:incentives.export')->name('incentives.export');
});


// ==========================================
// 🛡️ منطقة المطور فقط: إدارة الفئات والصلاحيات
// ==========================================
Route::middleware(['auth', 'developer'])->prefix('developer')->name('developer.')->group(function () {
    // إعدادات النظام
    Route::get('/settings', [DeveloperSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [DeveloperSettingsController::class, 'update'])->name('settings.update');

    Route::get('/roles', [RoleManagementController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleManagementController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleManagementController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}', [RoleManagementController::class, 'show'])->name('roles.show');
    Route::get('/roles/{role}/edit', [RoleManagementController::class, 'edit'])->name('roles.edit');
    Route::post('/roles/{role}/update', [RoleManagementController::class, 'update'])->name('roles.update');
    Route::post('/roles/{role}/delete', [RoleManagementController::class, 'destroy'])->name('roles.destroy');

    Route::post('/system-password', [RoleManagementController::class, 'updateSystemPassword'])->name('system_password.update');
    Route::post('/roles/{role}/members', [RoleManagementController::class, 'addMembers'])->name('roles.members.add');
    Route::post('/roles/{role}/members/{user}/remove', [RoleManagementController::class, 'removeMember'])->name('roles.members.remove');
    Route::post('/users/{user}/role', [RoleManagementController::class, 'changeUserRole'])->name('users.role');

    // مجموعات الشيفتات
    Route::get('/shifts', [DeveloperShiftGroupController::class, 'index'])->name('shifts.index');
    Route::get('/shifts/create', [DeveloperShiftGroupController::class, 'create'])->name('shifts.create');
    Route::post('/shifts', [DeveloperShiftGroupController::class, 'store'])->name('shifts.store');
    Route::get('/shifts/{shift}/edit', [DeveloperShiftGroupController::class, 'edit'])->name('shifts.edit');
    Route::post('/shifts/{shift}/update', [DeveloperShiftGroupController::class, 'update'])->name('shifts.update');
    Route::post('/shifts/{shift}/delete', [DeveloperShiftGroupController::class, 'destroy'])->name('shifts.destroy');
    Route::post('/shifts/users/{user}/assign', [DeveloperShiftGroupController::class, 'assignUser'])->name('shifts.assign_user');
});

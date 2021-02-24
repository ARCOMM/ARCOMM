<?php

//--- Home
Route::get('/', 'PageController@index');

Route::get('/arma3sync', function () {
    return redirect('https://docs.google.com/document/d/1i-LzCJE0l_7PtOj8WU717mmmzX1U2KaaNGEnj0KzkIw/view');
});

Route::get('/api/absence', 'API\AbsenceController@index');

//--- Shared Missions
Route::get('/share/{ref}', 'ShareController@show');
Route::get('/share/{ref}/{panel}', 'SharePanelController@show');
Route::get('/share/{ref}/briefing/{faction}', 'ShareBriefingController@show');

//--- Steam Authentication
Route::get('/steamauth', 'AuthController@login');

//--- Public Applications
Route::get('/join/acknowledged', 'PublicJoinController@acknowledged');
Route::resource('join', 'PublicJoinController', [
    'only' => ['index', 'store', 'create']
]);

//--- Media
Route::resource('media', 'MediaController');
Route::post('/media/delete', 'MediaController@deletePhoto');

//--- Roster
Route::get('/roster', 'PageController@roster');

//--- Attendance
Route::group(['middleware' => 'permission:attendance:view'], function () {
    Route::resource('/hub/attendance', 'Users\AttendanceController');
});

//--- Admins
Route::group(['middleware' => 'permission:apps:all'], function () {
    // Route::get('/hub/applications/transfer', 'JoinController@transferOldRecords');

    Route::get('/hub/applications/api/items', 'Join\JoinController@items');
    Route::get('/hub/applications/show/{jr}', 'Join\JoinController@show');

    Route::post('/hub/applications/api/send-email', 'Join\JoinController@email');
    Route::get('/hub/applications/api/email-submissions', 'Join\JoinController@emailSubmissions');

    // Statuses
    Route::post('/hub/applications/api/status', 'Join\JoinStatusController@store');
    Route::put('/hub/applications/api/{jr}/status', 'Join\JoinStatusController@update');
    Route::get('/hub/applications/api/{jr}/status', 'Join\JoinStatusController@show');

    Route::get('/hub/applications/{status}', 'Join\JoinController@index');

    Route::resource('/hub/applications', 'Join\JoinController');
});

Route::group(['middleware' => 'permission:apps:emails'], function () {
    // Email Templates
    Route::resource('/hub/applications/api/emails', 'Join\EmailTemplateController');
});

//--- Operations
Route::group(['middleware' => 'permission:operations:all'], function () {
    Route::get('/hub/operations', 'Missions\OperationController@index');
});

Route::group(['middleware' => 'permission:operations:all'], function () {
    Route::resource('/api/operations', 'API\OperationController');
    Route::resource('/api/operations/missions', 'API\OperationMissionController');
});

//--- Missions
Route::group(['middleware' => 'member'], function () {
    // Mission Media
    Route::post('/hub/missions/media/add-photo', 'Missions\MediaController@uploadPhoto');
    Route::post('/hub/missions/media/delete-photo', 'Missions\MediaController@deletePhoto');
    Route::post('/hub/missions/media/add-video', 'Missions\MediaController@addVideo');
    Route::post('/hub/missions/media/delete-video', 'Missions\MediaController@removeVideo');

    // Mission Operations
    Route::post('/hub/missions/operations/remove-mission', 'Missions\OperationController@removeMission');
    Route::post('/hub/missions/operations/add-mission', 'Missions\OperationController@addMission');
    Route::post('/hub/missions/operations/create-operation', 'Missions\OperationController@create');
    Route::post('/hub/missions/operations/delete-operation', 'Missions\OperationController@destroy');

    // Mission Comments
    Route::resource('/hub/missions/comments', 'Missions\CommentController', [
        'except' => ['create', 'show', 'update']
    ]);

    // Mission Briefings
    Route::post('/hub/missions/briefing', 'Missions\MissionController@briefing');
    Route::post('/hub/missions/briefing/update', 'Missions\MissionController@setBriefingLock');

    // Mission ORBAT
    Route::post('/hub/missions/orbat', 'Missions\MissionController@orbat');

    // Missions
    Route::get('/hub/missions/{mission}/delete', 'Missions\MissionController@destroy');
    Route::post('/hub/missions/{mission}/update', 'Missions\MissionController@update');
    Route::post('/hub/missions/{mission}/set-verification', 'Missions\MissionController@updateVerification');

    // Downlaod
    Route::get('/hub/missions/{mission}/download/{format}', 'Missions\MissionController@download');

    // Notes
    Route::get('/hub/missions/{mission}/notes/read-notifications', 'Missions\NoteController@readNotifications');
    Route::resource('/hub/missions/{mission}/notes', 'Missions\NoteController');

    // Panels
    Route::get('/hub/missions/{mission}/{panel}', 'Missions\MissionController@panel');

    // Absence
    Route::resource('/hub/absence', 'AbsenceController');

    Route::resource('/hub/missions', 'Missions\MissionController', [
        'except' => ['create', 'edit']
    ]);

    Route::get('/hub/settings/avatar-sync', 'Users\SettingsController@avatarSync');
    Route::resource('/hub/settings', 'Users\SettingsController');

    Route::get('/hub/guides', function () {
        return view('guides.index');
    });

    Route::get('/hub/notifications', 'Users\NotificationsController@index');

    // Hub Index
    Route::resource('/hub', 'HubController', [
        'only' => ['index']
    ]);
});

//--- User Management
Route::group(['middleware' => 'permission:users:all'], function () {
    Route::resource('/hub/users', 'Users\UserController');
});

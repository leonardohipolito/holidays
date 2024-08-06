<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

test('fail to list holidays without the required permissions', function () {
    $user = User::factory()->create();
    Sanctum::actingAs(
        $user,
        []
    );

    getJson('api/holiday')
        ->assertForbidden();
});
test('list all user holidays', function () {
    $user = User::factory()
        ->has(\App\Models\Holiday::factory()->count(3))
        ->create();
    Sanctum::actingAs(
        $user,
        ['holiday:viewAny']
    );

    getJson('api/holiday')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'description', 'date', 'location']
            ]
        ])
        ->assertJsonMissingPath('data.*.user_id');
});

test('list all user holidays with participants', function () {
    $user = User::factory()
        ->has(
            \App\Models\Holiday::factory()
                ->count(3)
                ->has(\App\Models\Participant::factory()->count(2))
        )
        ->create();
    Sanctum::actingAs(
        $user,
        ['holiday:viewAny']
    );

    getJson('api/holiday')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'date',
                    'location',
                    'participants'
                ]
            ]
        ])
        ->assertJsonCount(3, 'data.*.participants');
});

test('only list holidays for the authenticated user', function () {
    $user = User::factory()
        ->has(\App\Models\Holiday::factory()->count(3))
        ->create();
    $anotherUser = User::factory()
        ->has(\App\Models\Holiday::factory()->count(2))
        ->create();
    Sanctum::actingAs(
        $user,
        ['holiday:viewAny']
    );

    getJson('api/holiday')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'description', 'date', 'location']
            ]
        ])
        ->assertJsonMissing($anotherUser->holidays->pluck('id')->toArray());
});


test('create a new holiday with participants', function () {
    $user = User::factory()->create();
    Sanctum::actingAs(
        $user,
        ['holiday:create']
    );

    $holiday = \App\Models\Holiday::factory()->make();
    $participants = \App\Models\Participant::factory()->count(2)->make();

    postJson('api/holiday', [
        'title' => $holiday->title,
        'description' => $holiday->description,
        'date' => $holiday->date,
        'location' => $holiday->location,
        'participants' => $participants->toArray()
    ])->assertCreated()
        ->assertJsonFragment([
            'title' => $holiday->title,
            'description' => $holiday->description,
            'date' => $holiday->date,
            'location' => $holiday->location
        ])
        ->assertJsonCount(2, 'data.participants');
});

test('only create holidays for the authenticated user', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    Sanctum::actingAs(
        $user,
        ['holiday:create']
    );

    $holiday = \App\Models\Holiday::factory()->make();
    $participants = \App\Models\Participant::factory()->count(2)->make();

    $response = postJson('api/holiday', [
        'title' => $holiday->title,
        'description' => $holiday->description,
        'date' => $holiday->date,
        'location' => $holiday->location,
        'participants' => $participants->toArray(),
        'user_id' => $anotherUser->id
    ]);
    $response
        ->assertCreated()
        ->assertJsonFragment([
            'title' => $holiday->title,
            'description' => $holiday->description,
            'date' => $holiday->date,
            'location' => $holiday->location
        ]);
    Sanctum::actingAs(
        $anotherUser,
        ['holiday:viewAny']
    );

    getJson('api/holiday')
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

test('validate holiday creation', function () {
    $user = User::factory()->create();
    Sanctum::actingAs(
        $user,
        ['holiday:create']
    );

    postJson('api/holiday', [
        'participants' => [
            [
                'invalid' => 'value'
            ]
        ]
    ])->assertStatus(422)
        ->assertJsonValidationErrors([
            'title',
            'description',
            'date',
            'location',
            'participants.0.name',
        ]);
});

test('not duplicate participants', function () {
    $user = User::factory()->create();
    Sanctum::actingAs(
        $user,
        ['holiday:create']
    );

    $participants = \App\Models\Participant::factory()->count(2)->make();

    postJson('api/holiday', [
        'title' => 'Holiday Title',
        'description' => 'Holiday Description',
        'date' => '2021-12-25',
        'location' => 'Holiday Location',
        'participants' => [
            $participants[0]->toArray(),
            $participants[0]->toArray()
        ]
    ])
        ->assertCreated()
        ->assertJsonCount(1, 'data.participants');

    assertDatabaseCount('participants', 1);
});

test('fail to create a holiday without the required permissions', function () {
    $user = User::factory()->create();
    Sanctum::actingAs(
        $user,
        []
    );

    postJson('api/holiday', [])
        ->assertForbidden();
});
test('fail to retrieve a another user holiday', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    $holiday = \App\Models\Holiday::factory()
        ->for($anotherUser)
        ->create();
    Sanctum::actingAs(
        $user,
        ['holiday:view']
    );

    getJson("api/holiday/{$holiday->id}")
        ->assertNotFound();
});
test('fail to retrieve a holiday without the required permissions', function () {
    $user = User::factory()->create();
    $holiday = \App\Models\Holiday::factory()
        ->for($user)
        ->create();
    Sanctum::actingAs(
        $user,
        []
    );

    getJson("api/holiday/{$holiday->id}")
        ->assertForbidden();
});
test('retrieve a holiday', function () {
    $user = User::factory()->create();
    $holiday = \App\Models\Holiday::factory()
        ->for($user)
        ->create();
    Sanctum::actingAs(
        $user,
        ['holiday:view']
    );

    getJson("api/holiday/{$holiday->id}")
        ->assertSuccessful()
        ->assertJsonFragment([
            'id' => $holiday->id,
            'title' => $holiday->title,
            'description' => $holiday->description,
            'date' => $holiday->date,
            'location' => $holiday->location
        ]);
    
    $participants = \App\Models\Participant::factory()->count(2)->create();
    $holiday->participants()->attach($participants->pluck('id'));

    getJson("api/holiday/{$holiday->id}")
        ->assertSuccessful()
        ->assertJsonCount(2, 'data.participants');
});

test('fail to update a holiday without the required permissions', function () {
    $user = User::factory()->create();
    $holiday = \App\Models\Holiday::factory()
        ->for($user)
        ->create();
    Sanctum::actingAs(
        $user,
        []
    );

    putJson("api/holiday/{$holiday->id}", [])
        ->assertForbidden();
});
test('fail to update a another user holiday', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    $holiday = \App\Models\Holiday::factory()
        ->for($anotherUser)
        ->create();
    Sanctum::actingAs(
        $user,
        ['holiday:update']
    );

    putJson("api/holiday/{$holiday->id}", [])
        ->assertNotFound();
});

test('fail update a holiday with invalid data', function () {
    $user = User::factory()->create();
    $holiday = \App\Models\Holiday::factory()
        ->for($user)
        ->create();
    Sanctum::actingAs(
        $user,
        ['holiday:update']
    );

    putJson("api/holiday/{$holiday->id}", [
        'participants' => [
            [
                'invalid' => 'value'
            ]
        ]
    ])->assertStatus(422)
        ->assertJsonValidationErrors([
            'title',
            'description',
            'date',
            'location',
            'participants.0.name',
        ]);
});

test('update a holiday', function () {
    $user = User::factory()->create();
    $holiday = \App\Models\Holiday::factory()
        ->for($user)
        ->create();
    Sanctum::actingAs(
        $user,
        ['holiday:update']
    );

    $newHoliday = \App\Models\Holiday::factory()->make();
    $participants = \App\Models\Participant::factory()->count(2)->create();
    $holiday->participants()->attach($participants->pluck('id'));

    putJson("api/holiday/{$holiday->id}", [
        'title' => $newHoliday->title,
        'description' => $newHoliday->description,
        'date' => $newHoliday->date,
        'location' => $newHoliday->location,
        'participants' => $participants->toArray()
    ])
        ->assertSuccessful()
        ->assertJsonFragment([
            'title' => $newHoliday->title,
            'description' => $newHoliday->description,
            'date' => $newHoliday->date,
            'location' => $newHoliday->location
        ])
        ->assertJsonCount(2, 'data.participants');
});

test('fail to delete a holiday without the required permissions', function () {
    $user = User::factory()->create();
    $holiday = \App\Models\Holiday::factory()
        ->for($user)
        ->create();
    Sanctum::actingAs(
        $user,
        []
    );

    deleteJson("api/holiday/{$holiday->id}")
        ->assertForbidden();
});

test('fail to delete a another user holiday', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    $holiday = \App\Models\Holiday::factory()
        ->for($anotherUser)
        ->create();
    Sanctum::actingAs(
        $user,
        ['holiday:delete']
    );

    deleteJson("api/holiday/{$holiday->id}")
        ->assertNotFound();
});

test('delete a holiday', function () {
    $user = User::factory()->create();
    $holiday = \App\Models\Holiday::factory()
        ->for($user)
        ->create();
    Sanctum::actingAs(
        $user,
        ['holiday:delete']
    );

    deleteJson("api/holiday/{$holiday->id}")
        ->assertNoContent();
    assertDatabaseEmpty('holidays');
});
<?php

use App\Jobs\CreateCsvFile;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

it('can dispatch student CSV export job', function () {

    Queue::fake([CreateCsvFile::class]);

    $user = User::factory()->create();

    $this->actingAs($user);

    $response =  $this->actingAs($user)->postJson('api/students/generate-students-csv-file');

    $response->assertStatus(201)->assertJson(['message' => 'Creating csv file']);

    Queue::assertPushed(CreateCsvFile::class);
});

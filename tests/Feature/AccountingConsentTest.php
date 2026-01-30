<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ConsentHistory;
use App\Models\AccountingData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Database\Seeders\Test\AccountingTestSeeder;

class AccountingConsentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AccountingTestSeeder::class);
        Storage::fake('private');
    }

    /** @test */
    public function accounting_user_can_generate_consent_pdf()
    {
        $accounting = User::factory()->create();
        $accounting->assignRole('accounting');

        $teacher = User::factory()->teacher()->create();

        AccountingData::create([
            'teacher_id' => $teacher->id,
            'key' => 'tax_id',
            'value' => '12345678A',
        ]);

        $this->actingAs($accounting)
            ->post(route('accounting.teachers.consent.pdf', $teacher))
            ->assertRedirect();

        $this->assertDatabaseCount('consent_histories', 1);

        $consent = ConsentHistory::first();

        Storage::disk('private')->assertExists($consent->document_path);
    }

    /** @test */
public function consent_pdf_is_generated_only_once_per_season()
{
    $accounting = User::factory()->create();
    $accounting->assignRole('accounting');

    $teacher = User::factory()->teacher()->create();


    $this->actingAs($accounting)
        ->post(route('accounting.teachers.consent.pdf', $teacher));

    $this->assertDatabaseCount('consent_histories', 1);
}


    /** @test */
    public function accounting_user_can_view_consent_history()
    {
        $accounting = User::factory()->create();
        $accounting->assignRole('accounting');

        $teacher = User::factory()->teacher()->create();

        ConsentHistory::create([
            'teacher_id' => $teacher->id,
            'season' => '2025-2026',
            'document_path' => 'consents/test.pdf',
            'accepted_at' => now(),
            'checksum' => 'fakehash',
        ]);

        $this->actingAs($accounting)
            ->get(route('accounting.teachers.consents', $teacher))
            ->assertStatus(200)
            ->assertSee('2025-2026');
    }

    /** @test */
    public function non_accounting_user_cannot_access_consent_system()
    {
        $user = User::factory()->create();
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($user)
            ->post(route('accounting.teachers.consent.pdf', $teacher))
            ->assertForbidden();
    }
}

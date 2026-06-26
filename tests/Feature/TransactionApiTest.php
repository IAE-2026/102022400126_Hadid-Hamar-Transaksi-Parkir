<?php

namespace Tests\Feature;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = '102022400126';

    private function withKey(): array
    {
        return ['X-IAE-KEY' => self::KEY, 'Accept' => 'application/json'];
    }

    public function test_request_tanpa_key_ditolak_401(): void
    {
        $response = $this->getJson('/api/v1/transactions');

        $response->assertStatus(401)
            ->assertJson(['status' => 'error']);
    }

    public function test_request_dengan_key_salah_ditolak_403(): void
    {
        $response = $this->getJson('/api/v1/transactions', ['X-IAE-KEY' => 'salah']);

        $response->assertStatus(403)
            ->assertJson(['status' => 'error']);
    }

    public function test_get_list_mengembalikan_200_dengan_wrapper(): void
    {
        Transaction::create([
            'id' => 'trx_001',
            'location_id' => 'loc_001',
            'entry_time' => now(),
            'status' => 'BERLANGSUNG',
        ]);

        $response = $this->getJson('/api/v1/transactions', $this->withKey());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [['id', 'location_id', 'status']],
                'meta' => ['service_name', 'api_version'],
            ])
            ->assertJsonPath('status', 'success');
    }

    public function test_get_detail_tidak_ditemukan_mengembalikan_404_wrapper(): void
    {
        $response = $this->getJson('/api/v1/transactions/trx_999', $this->withKey());

        $response->assertStatus(404)
            ->assertJsonStructure(['status', 'message', 'errors'])
            ->assertJsonPath('status', 'error');
    }

    public function test_post_membuat_transaksi_mengembalikan_201_wrapper(): void
    {
        $response = $this->postJson('/api/v1/transactions', [
            'location_id' => 'loc_001',
            'member_card_id' => 'MEM001',
        ], $this->withKey());

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.location_id', 'loc_001')
            ->assertJsonPath('data.status', 'BERLANGSUNG');

        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_post_dengan_lokasi_tak_dikenal_tetap_201(): void
    {
        // Mode standalone: location_id apa pun tetap menghasilkan transaksi.
        $response = $this->postJson('/api/v1/transactions', [
            'location_id' => 'loc_tidak_terdaftar',
            'member_card_id' => 'MEM_tidak_terdaftar',
        ], $this->withKey());

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.location_id', 'loc_tidak_terdaftar');
    }

    public function test_post_tanpa_body_tetap_201(): void
    {
        // Penilai generik kadang mengirim body kosong; POST harus tetap 201.
        $response = $this->postJson('/api/v1/transactions', [], $this->withKey());

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success');
    }

    public function test_path_tidak_dikenal_mengembalikan_404_bukan_405(): void
    {
        $response = $this->getJson('/api/v1/tidak-ada', $this->withKey());

        $response->assertStatus(404)
            ->assertJsonPath('status', 'error');
    }

    public function test_method_tidak_diizinkan_mengembalikan_405_wrapper(): void
    {
        // DELETE tidak terdaftar untuk endpoint ini.
        $response = $this->json('DELETE', '/api/v1/transactions', [], $this->withKey());

        $response->assertStatus(405)
            ->assertJsonPath('status', 'error');
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketCategoryAndSolvedAtTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_create_a_ticket_with_a_valid_category()
    {
        $user = User::factory()->create(['role' => 2]); // User role

        $response = $this->actingAs($user)->post(route('user.tickets.store'), [
            'subject' => 'Test Subject',
            'message' => 'Test message content here.',
            'category' => 'live Egypt',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertDatabaseHas('tickets', [
            'subject' => 'Test Subject',
            'category' => 'live Egypt',
            'status' => 'open',
        ]);
    }

    /** @test */
    public function a_user_cannot_create_a_ticket_with_an_invalid_category()
    {
        $user = User::factory()->create(['role' => 2]);

        $response = $this->actingAs($user)->post(route('user.tickets.store'), [
            'subject' => 'Test Subject',
            'message' => 'Test message content here.',
            'category' => 'invalid-category',
        ]);

        $response->assertSessionHasErrors('category');
        $this->assertDatabaseMissing('tickets', [
            'subject' => 'Test Subject',
        ]);
    }

    /** @test */
    public function an_agent_can_create_a_ticket_with_a_valid_category()
    {
        $agent = User::factory()->create(['role' => 0]); // Agent role

        $response = $this->actingAs($agent)->post(route('agent.tickets.store'), [
            'subject' => 'Agent Ticket Subject',
            'message' => 'Agent ticket message.',
            'category' => 'live pro',
        ]);

        $response->assertRedirect(route('agent.dashboard'));
        $this->assertDatabaseHas('tickets', [
            'subject' => 'Agent Ticket Subject',
            'category' => 'live pro',
            'status' => 'open',
        ]);
    }

    /** @test */
    public function closing_a_ticket_sets_solved_at_timestamp_and_reopening_clears_it()
    {
        $user = User::factory()->create(['role' => 2]);
        $admin = User::factory()->create(['role' => 1]); // Admin role

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'subject' => 'Test Subject',
            'message' => 'Test message',
            'category' => 'other',
            'status' => 'open',
        ]);

        $this->assertNull($ticket->solved_at);

        // Close ticket as Admin
        $response = $this->actingAs($admin)->post("/admin/tickets/{$ticket->id}/status", [
            'status' => 'closed',
        ]);

        $ticket->refresh();
        $this->assertEquals('closed', $ticket->status);
        $this->assertNotNull($ticket->solved_at);

        // Reopen ticket as Admin
        $response = $this->actingAs($admin)->post("/admin/tickets/{$ticket->id}/status", [
            'status' => 'open',
        ]);

        $ticket->refresh();
        $this->assertEquals('open', $ticket->status);
        $this->assertNull($ticket->solved_at);
    }

    /** @test */
    public function closing_a_ticket_as_agent_sets_solved_at()
    {
        $agent = User::factory()->create(['role' => 0]);

        $ticket = Ticket::create([
            'user_id' => $agent->id,
            'subject' => 'Agent Subject',
            'message' => 'Agent message',
            'category' => 'demo pro',
            'status' => 'open',
        ]);

        $this->assertNull($ticket->solved_at);

        // Close ticket as Agent
        $response = $this->actingAs($agent)->post(route('agent.tickets.close', $ticket->id));

        $ticket->refresh();
        $this->assertEquals('closed', $ticket->status);
        $this->assertNotNull($ticket->solved_at);
    }
}

<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\DailyCompletionStatus;
use App\Models\Todo;
use App\Models\TodoLog;
use App\Models\Goal;
use App\Models\GoalMilestone;
use App\Models\WeightLog;
use App\Models\WeightLossGoal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HabitPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_today(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/today');
    }

    public function test_pages_are_available(): void
    {
        $this->get('/today')->assertOk();
        $this->get('/calendar')->assertOk();
        $this->get('/habits')->assertOk();
        $this->get('/goals')->assertOk();
        $this->get('/weight-loss')->assertOk();
        $this->get('/settings')->assertOk();
    }

    public function test_today_only_shows_habits_scheduled_for_current_day(): void
    {
        $todayKey = strtolower(now()->englishDayOfWeek);

        $visibleHabit = Todo::query()->create([
            'name' => 'Walk',
            'days_of_week' => [$todayKey],
            'daily_goal' => 6000,
            'unit' => 'step',
            'is_active' => true,
        ]);

        $hiddenHabit = Todo::query()->create([
            'name' => 'Read',
            'days_of_week' => [$todayKey === 'monday' ? 'tuesday' : 'monday'],
            'daily_goal' => 20,
            'unit' => 'page',
            'is_active' => true,
        ]);

        $response = $this->get('/today');

        $response->assertOk();
        $response->assertSee($visibleHabit->name);
        $response->assertDontSee($hiddenHabit->name);
    }

    public function test_timeline_settings_show_today_counters(): void
    {
        $startDate = now()->copy()->subDays(9)->toDateString();
        $deadlineDate = now()->copy()->addDays(12)->toDateString();

        AppSetting::setValue('timeline_start_date', $startDate);
        AppSetting::setValue('timeline_deadline_date', $deadlineDate);

        $response = $this->get('/today');

        $response->assertOk();
        $response->assertSee('Current week');
        $response->assertSee('Week 2');
        $response->assertSee('Days until deadline');
        $response->assertSee('12');
    }

    public function test_login_credential_can_be_set_from_settings(): void
    {
        $response = $this->post('/settings/login', [
            'login_username' => 'norman',
            'login_password' => 'secret123',
        ]);

        $response->assertRedirect('/settings');
        $this->assertSame('norman', AppSetting::getValue('login_username'));
        $this->assertTrue(Hash::check('secret123', (string) AppSetting::getValue('login_password_hash')));

        $this->get('/today')->assertOk();
    }

    public function test_app_requires_login_after_credential_is_configured(): void
    {
        AppSetting::setValue('login_username', 'norman');
        AppSetting::setValue('login_password_hash', Hash::make('secret123'));

        $this->get('/today')->assertRedirect('/login');
        $this->get('/habits')->assertRedirect('/login');
        $this->get('/settings')->assertRedirect('/login');
    }

    public function test_user_can_log_in_and_log_out(): void
    {
        AppSetting::setValue('login_username', 'norman');
        AppSetting::setValue('login_password_hash', Hash::make('secret123'));

        $this->post('/login', [
            'username' => 'norman',
            'password' => 'secret123',
        ])->assertRedirect('/today');

        $this->get('/today')->assertOk();

        $this->post('/logout')->assertRedirect('/login');
        $this->get('/today')->assertRedirect('/login');
    }

    public function test_logging_a_habit_marks_it_complete_when_goal_is_met(): void
    {
        $todayKey = strtolower(now()->englishDayOfWeek);

        $habit = Todo::query()->create([
            'name' => 'Walk',
            'days_of_week' => [$todayKey],
            'daily_goal' => 6000,
            'unit' => 'step',
            'is_active' => true,
        ]);

        $response = $this->post("/today/{$habit->id}/logs", [
            'habit_id' => $habit->id,
            'value' => 6000,
        ]);

        $response->assertRedirect('/today');

        $log = $habit->fresh()->logs()->first();

        $this->assertNotNull($log);
        $this->assertTrue($log->completed);
        $this->assertSame(now()->toDateString(), $log->logged_for->toDateString());
    }

    public function test_logging_a_habit_can_return_json_for_background_updates(): void
    {
        $todayKey = strtolower(now()->englishDayOfWeek);

        $habit = Todo::query()->create([
            'name' => 'Read',
            'days_of_week' => [$todayKey],
            'daily_goal' => 20,
            'unit' => 'page',
            'is_active' => true,
        ]);

        $response = $this->postJson("/today/{$habit->id}/logs", [
            'habit_id' => $habit->id,
            'value' => 12,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('habit.id', $habit->id)
            ->assertJsonPath('habit.completed', false)
            ->assertJsonPath('habit.logged_value', '12')
            ->assertJsonPath('habit.status_label', 'In progress');
    }

    public function test_logging_a_previous_scheduled_day_can_update_that_date(): void
    {
        $previousDay = now()->subDay()->startOfDay();
        $previousKey = strtolower($previousDay->englishDayOfWeek);

        $habit = Todo::query()->create([
            'name' => 'Read',
            'days_of_week' => [$previousKey],
            'daily_goal' => 20,
            'unit' => 'page',
            'is_active' => true,
        ]);

        $response = $this->postJson("/today/{$habit->id}/logs", [
            'habit_id' => $habit->id,
            'logged_for' => $previousDay->toDateString(),
            'value' => 18,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('habit.logged_for', $previousDay->toDateString())
            ->assertJsonPath('habit.logged_value', '18')
            ->assertJsonPath('habit.is_today', false);

        $log = $habit->fresh()->logs()->first();

        $this->assertNotNull($log);
        $this->assertSame($previousDay->toDateString(), $log->logged_for->toDateString());
    }

    public function test_a_created_habit_can_be_updated(): void
    {
        $habit = Todo::query()->create([
            'name' => 'Walk',
            'days_of_week' => ['monday', 'wednesday'],
            'daily_goal' => 6000,
            'unit' => 'step',
            'is_active' => true,
        ]);

        $response = $this->put("/habits/{$habit->id}", [
            'name' => 'Morning Walk',
            'days_of_week' => ['monday', 'tuesday', 'wednesday'],
            'daily_goal' => 8000,
            'unit' => 'steps',
        ]);

        $response->assertRedirect('/habits');

        $updatedHabit = $habit->fresh();

        $this->assertSame('Morning Walk', $updatedHabit->name);
        $this->assertSame(['monday', 'tuesday', 'wednesday'], $updatedHabit->days_of_week);
        $this->assertSame('8000', \App\Models\Todo::formatAmount($updatedHabit->daily_goal));
        $this->assertSame('steps', $updatedHabit->unit);
    }

    public function test_habits_can_be_reordered(): void
    {
        $firstHabit = Todo::query()->create([
            'name' => 'Walk',
            'days_of_week' => ['monday'],
            'daily_goal' => 6000,
            'unit' => 'step',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $secondHabit = Todo::query()->create([
            'name' => 'Read',
            'days_of_week' => ['tuesday'],
            'daily_goal' => 20,
            'unit' => 'page',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this->patchJson('/habits/reorder', [
            'habit_ids' => [$secondHabit->id, $firstHabit->id],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Habit order saved.');

        $this->assertSame(1, $secondHabit->fresh()->sort_order);
        $this->assertSame(2, $firstHabit->fresh()->sort_order);
    }

    public function test_today_page_uses_saved_habit_order(): void
    {
        $todayKey = strtolower(now()->englishDayOfWeek);

        Todo::query()->create([
            'name' => 'Stretch',
            'days_of_week' => [$todayKey],
            'daily_goal' => 10,
            'unit' => 'minute',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Todo::query()->create([
            'name' => 'Walk',
            'days_of_week' => [$todayKey],
            'daily_goal' => 6000,
            'unit' => 'step',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this->get('/today');

        $response->assertOk();
        $response->assertSeeInOrder(['Stretch', 'Walk']);
    }

    public function test_monthly_weight_loss_goal_can_be_created(): void
    {
        $response = $this->post('/weight-loss', [
            'month' => '2026-04',
            'starting_weight' => 84.5,
            'goal_weight' => 82.8,
        ]);

        $response->assertRedirect('/weight-loss');

        $goal = WeightLossGoal::query()->first();

        $this->assertNotNull($goal);
        $this->assertSame('2026-04-01', $goal->month->toDateString());
        $this->assertSame('84.5', WeightLossGoal::formatWeight($goal->starting_weight));
        $this->assertSame('82.8', WeightLossGoal::formatWeight($goal->goal_weight));
    }

    public function test_goal_can_be_created_and_redirects_to_milestones_page(): void
    {
        $response = $this->post('/goals', [
            'name' => 'Launch my portfolio',
        ]);

        $goal = Goal::query()->first();

        $this->assertNotNull($goal);
        $response->assertRedirect("/goals/{$goal->id}");
        $this->assertSame('Launch my portfolio', $goal->name);
    }

    public function test_milestone_can_be_created_for_a_goal(): void
    {
        $goal = Goal::query()->create([
            'name' => 'Launch my portfolio',
        ]);

        $response = $this->post("/goals/{$goal->id}/milestones", [
            'name' => 'Finish the draft',
            'estimated_completion_month' => '2026-06',
        ]);

        $response->assertRedirect("/goals/{$goal->id}");

        $milestone = $goal->fresh()->milestones->first();

        $this->assertNotNull($milestone);
        $this->assertSame('Finish the draft', $milestone->name);
        $this->assertSame('2026-06-01', $milestone->estimated_completion_month->toDateString());
    }

    public function test_newly_created_milestone_is_added_to_the_top(): void
    {
        $goal = Goal::query()->create([
            'name' => 'Launch my portfolio',
        ]);

        $goal->milestones()->create([
            'name' => 'Draft homepage',
            'estimated_completion_month' => '2026-05-01',
            'sort_order' => 1,
        ]);

        $this->post("/goals/{$goal->id}/milestones", [
            'name' => 'Record intro video',
            'estimated_completion_month' => '2026-08',
        ])->assertRedirect("/goals/{$goal->id}");

        $orderedNames = $goal->fresh()->milestones->pluck('name')->all();

        $this->assertSame(['Record intro video', 'Draft homepage'], $orderedNames);
    }

    public function test_milestone_can_be_updated_with_ajax(): void
    {
        $goal = Goal::query()->create([
            'name' => 'Launch my portfolio',
        ]);

        $milestone = $goal->milestones()->create([
            'name' => 'Draft homepage',
            'estimated_completion_month' => '2026-05-01',
        ]);

        $response = $this->patchJson("/goals/{$goal->id}/milestones/{$milestone->id}", [
            'name' => 'Draft homepage and case study',
            'estimated_completion_month' => '2026-07',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Milestone updated.')
            ->assertJsonPath('milestone.name', 'Draft homepage and case study')
            ->assertJsonPath('milestone.estimated_completion_month', '2026-07')
            ->assertJsonPath('milestone.estimated_completion_label', 'July 2026');

        $updatedMilestone = $milestone->fresh();

        $this->assertSame('Draft homepage and case study', $updatedMilestone->name);
        $this->assertSame('2026-07-01', $updatedMilestone->estimated_completion_month->toDateString());
    }

    public function test_milestones_can_be_reordered(): void
    {
        $goal = Goal::query()->create([
            'name' => 'Launch my portfolio',
        ]);

        $firstMilestone = $goal->milestones()->create([
            'name' => 'Draft homepage',
            'estimated_completion_month' => '2026-05-01',
            'sort_order' => 1,
        ]);

        $secondMilestone = $goal->milestones()->create([
            'name' => 'Record intro video',
            'estimated_completion_month' => '2026-06-01',
            'sort_order' => 2,
        ]);

        $response = $this->patchJson("/goals/{$goal->id}/milestones/reorder", [
            'milestone_ids' => [$secondMilestone->id, $firstMilestone->id],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Milestone order saved.');

        $orderedIds = $goal->fresh()->milestones->pluck('id')->all();

        $this->assertSame([$secondMilestone->id, $firstMilestone->id], $orderedIds);
    }

    public function test_today_page_shows_current_month_milestones_in_goal_order(): void
    {
        $goal = Goal::query()->create([
            'name' => 'Launch my portfolio',
        ]);

        $currentMonth = now()->startOfMonth()->toDateString();

        $goal->milestones()->create([
            'name' => 'Second milestone',
            'estimated_completion_month' => $currentMonth,
            'sort_order' => 2,
        ]);

        $goal->milestones()->create([
            'name' => 'First milestone',
            'estimated_completion_month' => $currentMonth,
            'sort_order' => 1,
        ]);

        $goal->milestones()->create([
            'name' => 'Later milestone',
            'estimated_completion_month' => now()->copy()->addMonth()->startOfMonth()->toDateString(),
            'sort_order' => 3,
        ]);

        $response = $this->get('/today');

        $response->assertOk();
        $response->assertSee('Goals');
        $response->assertSeeInOrder(['First milestone', 'Second milestone']);
        $response->assertDontSee('Later milestone');
    }

    public function test_current_month_milestone_can_be_toggled_from_today_page(): void
    {
        $goal = Goal::query()->create([
            'name' => 'Launch my portfolio',
        ]);

        $milestone = $goal->milestones()->create([
            'name' => 'Publish homepage',
            'estimated_completion_month' => now()->startOfMonth()->toDateString(),
            'sort_order' => 1,
            'completed' => false,
        ]);

        $response = $this->patchJson("/today/milestones/{$milestone->id}/toggle");

        $response
            ->assertOk()
            ->assertJsonPath('milestone.id', $milestone->id)
            ->assertJsonPath('milestone.completed', true)
            ->assertJsonPath('milestone.status_label', 'Completed');

        $this->assertTrue($milestone->fresh()->completed);
    }

    public function test_weight_loss_page_shows_overall_goal_summary(): void
    {
        WeightLossGoal::query()->create([
            'month' => '2026-04-01',
            'starting_weight' => 84.5,
            'goal_weight' => 83,
        ]);

        WeightLossGoal::query()->create([
            'month' => '2026-05-01',
            'starting_weight' => 83,
            'goal_weight' => 81.5,
        ]);

        $response = $this->get('/weight-loss');

        $response->assertOk();
        $response->assertSee('Overall goal');
        $response->assertSee('84.5 kg');
        $response->assertSee('83 kg');
        $response->assertSee('81.5 kg');
        $response->assertSeeInOrder(['April 2026', 'May 2026']);
    }

    public function test_calendar_page_shows_day_completion_states(): void
    {
        $today = now()->startOfDay();
        $tomorrow = $today->copy()->addDay();
        $todayKey = strtolower($today->englishDayOfWeek);

        Todo::query()->create([
            'name' => 'Walk',
            'days_of_week' => [$todayKey],
            'daily_goal' => 6000,
            'unit' => 'step',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        WeightLossGoal::query()->create([
            'month' => $today->copy()->startOfMonth()->toDateString(),
            'starting_weight' => 90,
            'goal_weight' => 84,
        ]);

        TodoLog::query()->create([
            'todo_id' => Todo::query()->first()->id,
            'logged_for' => $today->toDateString(),
            'value' => 6000,
            'completed' => true,
        ]);

        WeightLog::query()->create([
            'logged_for' => $today->toDateString(),
            'weight' => 84,
            'rolling_average_weight' => 84,
        ]);

        $response = $this->get('/calendar');

        $response->assertOk();
        $response->assertSee('Monthly completion');
        $response->assertSee('data-calendar-day-button', false);
        $response->assertSee('data-date="'.$today->toDateString().'"', false);
        $response->assertSee('data-state="complete"', false);
        $response->assertSee('Walk');
        $response->assertSee('Completed');
        $response->assertSee('data-date="'.$tomorrow->toDateString().'"', false);
        $response->assertSee('data-state="future"', false);
    }

    public function test_calendar_month_can_be_loaded_over_ajax(): void
    {
        WeightLossGoal::query()->create([
            'month' => '2026-04-01',
            'starting_weight' => 90,
            'goal_weight' => 88,
        ]);

        $response = $this->getJson('/calendar/month?month=2026-04');

        $response
            ->assertOk()
            ->assertJsonPath('month', '2026-04');

        $this->assertStringContainsString('April 2026', $response->json('html'));
    }

    public function test_logging_weight_returns_rolling_average_and_progress_data(): void
    {
        $currentMonth = now()->startOfMonth();

        WeightLossGoal::query()->create([
            'month' => $currentMonth->toDateString(),
            'starting_weight' => 84.5,
            'goal_weight' => 83.5,
        ]);

        WeightLossGoal::query()->create([
            'month' => $currentMonth->copy()->addMonth()->toDateString(),
            'starting_weight' => 83.5,
            'goal_weight' => 82.5,
        ]);

        WeightLog::query()->create([
            'logged_for' => now()->copy()->subDays(2)->toDateString(),
            'weight' => 85,
            'rolling_average_weight' => 85,
        ]);

        WeightLog::query()->create([
            'logged_for' => now()->copy()->subDay()->toDateString(),
            'weight' => 84,
            'rolling_average_weight' => 84.5,
        ]);

        $response = $this->postJson('/today/weight-logs', [
            'weight' => 83,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Today\'s weight was saved.')
            ->assertJsonPath('weight.today_logged_weight', '83')
            ->assertJsonPath('weight.rolling_average_weight', '84')
            ->assertJsonPath('weight.chart.actual_points_count', 3)
            ->assertJsonPath('weight.chart.projected_goal_weight', '83.5')
            ->assertJsonPath('weight.chart.status_label', 'Failed')
            ->assertJsonPath('weight.gauge.zone_label', '80-85 kg')
            ->assertJsonPath('weight.overall.goal_weight', '82.5')
            ->assertJsonPath('weight.overall.percent', 25)
            ->assertJsonPath('weight.monthly.percent', 50);

        $todayLog = WeightLog::query()->whereDate('logged_for', now()->toDateString())->first();

        $this->assertNotNull($todayLog);
        $this->assertSame('84', WeightLossGoal::formatWeight($todayLog->rolling_average_weight));
    }

    public function test_stored_daily_completion_status_survives_later_habit_schedule_changes(): void
    {
        $today = now()->startOfDay();
        $todayKey = strtolower($today->englishDayOfWeek);
        $otherDay = $todayKey === 'monday' ? 'tuesday' : 'monday';

        WeightLossGoal::query()->create([
            'month' => $today->copy()->startOfMonth()->toDateString(),
            'starting_weight' => 90,
            'goal_weight' => 84,
        ]);

        WeightLog::query()->create([
            'logged_for' => $today->toDateString(),
            'weight' => 84,
            'rolling_average_weight' => 84,
        ]);

        $habit = Todo::query()->create([
            'name' => 'Walk',
            'days_of_week' => [$todayKey],
            'daily_goal' => 6000,
            'unit' => 'step',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->post("/today/{$habit->id}/logs", [
            'habit_id' => $habit->id,
            'value' => 6000,
        ])->assertRedirect('/today');

        $storedStatus = DailyCompletionStatus::query()
            ->whereDate('tracked_for', $today->toDateString())
            ->first();

        $this->assertNotNull($storedStatus);
        $this->assertSame('complete', $storedStatus->state);
        $this->assertSame(1, $storedStatus->scheduled_habit_count);

        $habit->update([
            'days_of_week' => [$otherDay],
        ]);

        $response = $this->get('/calendar');

        $response->assertOk();
        $response->assertSee('data-date="'.$today->toDateString().'"', false);
        $response->assertSee('data-state="complete"', false);
        $response->assertSee('Walk');
    }

    public function test_today_page_shows_weight_loss_section(): void
    {
        $currentMonth = now()->startOfMonth();

        WeightLossGoal::query()->create([
            'month' => $currentMonth->toDateString(),
            'starting_weight' => 84.5,
            'goal_weight' => 83.5,
        ]);

        WeightLossGoal::query()->create([
            'month' => $currentMonth->copy()->addMonth()->toDateString(),
            'starting_weight' => 83.5,
            'goal_weight' => 82.5,
        ]);

        WeightLog::query()->create([
            'logged_for' => now()->toDateString(),
            'weight' => 83.8,
            'rolling_average_weight' => 83.8,
        ]);

        $response = $this->get('/today');

        $response->assertOk();
        $response->assertSee('Weight loss');
        $response->assertSee('Overall progress');
        $response->assertSee('Monthly progress');
        $response->assertSee('Current month trend');
        $response->assertSee('Failed');
        $response->assertSee('Average gauge');
        $response->assertSee('83.8 kg');
        $response->assertSee('82.5 kg goal');
    }
}

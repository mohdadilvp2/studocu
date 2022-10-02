<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Console\Commands\FlashCardInteractive;

class FlashCardInteractiveTest extends TestCase
{

    use DatabaseTransactions;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_exit_and_invalid_menu()
    {
        $this->artisan('flashcard:interactive')
            ->expectsQuestion(FlashCardInteractive::generateMainMenuText(), '10')
            ->expectsQuestion(FlashCardInteractive::generateMainMenuText(), '6')
            ->assertExitCode(0);
    }
    public function test_create_question_fail()
    {
        $menuString = "Please enter a number";
        foreach (\App\Console\Commands\FlashCardInteractive::$mainMenu as $key => $menu) {
            $menuString .= "\n{$key} - {$menu}";
        }
        $this->artisan('flashcard:interactive')
            ->expectsQuestion($menuString, '1')
            ->expectsQuestion('Please enter the question ', null)
            ->expectsOutput('The question field is required.')
            ->expectsQuestion("Please enter the question ", 'Test question')
            ->expectsQuestion('Please enter the answer ', 'Test answer')
            ->expectsQuestion($menuString, '6')
            ->assertExitCode(0);
        $this->assertDatabaseHas('questions', [
            'question' => 'Test question',
            'answer' => 'Test answer'
        ]);
    }
    public function test_create_question()
    {
        $this->artisan('flashcard:interactive')
            ->expectsQuestion(FlashCardInteractive::generateMainMenuText(), '1')
            ->expectsQuestion('Please enter the question ', 'Test question')
            ->expectsQuestion('Please enter the answer ', 'Test answer')
            ->expectsQuestion(FlashCardInteractive::generateMainMenuText(), '6')
            ->assertExitCode(0);
        $this->assertDatabaseHas('questions', [
            'question' => 'Test question',
            'answer' => 'Test answer'
        ]);
    }

    public function test_list_question()
    {

        $this->artisan('flashcard:interactive')
            ->expectsQuestion(FlashCardInteractive::generateMainMenuText(), '2')
            ->expectsTable(
                ['ID', 'Question', 'Answer'],
                Question::all(['id', 'question', 'answer'])->toArray()
            )
            ->expectsQuestion(FlashCardInteractive::generateMainMenuText(), '6')
            ->assertExitCode(0);
    }

    public function test_reset()
    {

        $this->artisan('flashcard:interactive')
            ->expectsQuestion(FlashCardInteractive::generateMainMenuText(), '5')
            ->expectsQuestion(FlashCardInteractive::generateMainMenuText(), '6')
            ->assertExitCode(0);
    }

    public function test_practice()
    {
        $question = Question::factory()->create();
        $this->artisan('flashcard:interactive')
            ->expectsQuestion(FlashCardInteractive::generateMainMenuText(), '3')
            ->expectsOutput(' Enter 0 to go to main menu')
            ->expectsQuestion('Please select a question id', $question->id)
            ->expectsQuestion($question->question, $question->answer . "Incorrect")
            ->expectsOutput('Incorrect')

            ->expectsOutput(' Enter 0 to go to main menu')
            ->expectsQuestion('Please select a question id', $question->id)
            ->expectsQuestion($question->question, $question->answer)
            ->expectsOutput('Correct')

            ->expectsOutput(' Enter 0 to go to main menu')
            ->expectsQuestion('Please select a question id', '0')
            ->expectsQuestion(FlashCardInteractive::generateMainMenuText(), '6')
            ->assertExitCode(0);
    }

    public function test_stats()
    {
        $this->artisan('flashcard:interactive')
            ->expectsQuestion(FlashCardInteractive::generateMainMenuText(), '4')
            ->expectsOutput(Question::count() . '- The total amount of questions.')
            ->expectsOutput('0- % of questions that have an answer.')
            ->expectsOutput('0- % of questions that have a correct answer.')
            ->expectsQuestion(FlashCardInteractive::generateMainMenuText(), '6')
            ->assertExitCode(0);
        $this->test_practice();
        $this->artisan('flashcard:interactive')
            ->expectsQuestion(FlashCardInteractive::generateMainMenuText(), '4')
            ->expectsOutput(Question::count() . '- The total amount of questions.')
            ->expectsOutput('100- % of questions that have an answer.')
            ->expectsOutput('100- % of questions that have a correct answer.')
            ->expectsQuestion(FlashCardInteractive::generateMainMenuText(), '6')
            ->assertExitCode(0);
    }
}

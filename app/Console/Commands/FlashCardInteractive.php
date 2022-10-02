<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;
use App\Models\UserQuestion;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Illuminate\Support\Facades\Validator;


class FlashCardInteractive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashcard:interactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is an interactive command to create and practice flashcards.';

    /**
     * The mainmenu options
     *
     * @var array
     */
    public static $mainMenu = [
        1 => 'Create a flashcard',
        2 => 'List all flashcards',
        3 => 'Practice',
        4 => 'Stats',
        5 => 'Reset',
        6 => 'Exit',
    ];

    /**
     * This is the uniqueId we generate while starting our artisan command, We use this to track user progress
     *
     * @var string
     */
    private $uniqueUserId = '';

    public function __construct()
    {
        parent::__construct();
        $this->uniqueUserId = uniqid(time());
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): mixed
    {
        $this->showMainMenu();
        return 0;
    }

    /**
     * Return main menu option string
     *
     * @return string
     */
    public static function generateMainMenuText(): string
    {
        $menuString = "Please enter a number";
        foreach (self::$mainMenu as $key => $menu) {
            $menuString .= "\n{$key} - {$menu}";
        }
        return $menuString;
    }

    /**
     * Function to show main menu
     *
     * @return void
     */
    public function showMainMenu(): void
    {
        $menuString = self::generateMainMenuText();
        $menuOption = $this->ask($menuString);
        while (!in_array($menuOption, array_keys(self::$mainMenu))) {
            $menuOption  = $this->ask($menuString);
        }
        switch ($menuOption) {
                // Create question
            case 1:
                $this->createQuestion();
                break;
            case 2:
                // List all exting questions
                $this->listAllQuestions();
                break;
            case 3:
                // Practice
                $this->practice();
                break;
            case 4:
                // Show stats
                $this->showStats();
                break;
            case 5:
                // Reset Practice
                $this->resetPractice();
                break;
            case 6:
                return;
        }
    }

    /**
     * Function to list current user stats
     *
     * @return void
     */
    public function showStats(): void
    {
        $totalQuestions = Question::totalQuestions();
        $userQuestionDeatils = UserQuestion::where('user_id', $this->uniqueUserId)
            ->select('user_questions.status')
            ->get();
        $answerPercentage = $this->getPercentage($userQuestionDeatils->count(), $totalQuestions);
        $correctAnswerPercentage = $this->getPercentage($userQuestionDeatils->where('status', UserQuestion::STATUS_CORRECT)->count(), $totalQuestions);
        $this->info("{$totalQuestions}- The total amount of questions.");
        $this->info("{$answerPercentage}- % of questions that have an answer.");
        $this->info("{$correctAnswerPercentage}- % of questions that have a correct answer.");
        $this->showMainMenu();
    }

    /**
     * Function used to practice questions and check if user answered it correctly
     *
     * @param  int  $questionId
     * @return void
     */
    public function practiceQuestion(int $questionId): void
    {
        $question = Question::find($questionId);
        $userQuestion = UserQuestion::where('question_id', $questionId)->where('user_id', $this->uniqueUserId)->first();
        if ($userQuestion && $userQuestion->status) {
            $this->error("This question aleady answerd correctly, Please pick another question.");
            $this->practice();
        }
        $questionAnswerCreate = [];
        if ($question->answer == $this->ask($question->question)) {
            $this->info("Correct");
            if ($userQuestion) {
                $userQuestion->status = UserQuestion::STATUS_CORRECT;
                $userQuestion->save();
            } else {
                $questionAnswerCreate = [
                    'status' => UserQuestion::STATUS_CORRECT,
                    'question_id' => $questionId,
                    'user_id' => $this->uniqueUserId
                ];
            }
        } else {
            $this->error("Incorrect");
            if ($userQuestion) {
                $userQuestion->status = UserQuestion::STATUS_INCORRECT;
                $userQuestion->save();
            } else {
                $questionAnswerCreate = [
                    'status' => UserQuestion::STATUS_INCORRECT,
                    'question_id' => $questionId,
                    'user_id' => $this->uniqueUserId
                ];
            }
        }
        // If $questionAnswerCreate is not empty, we need to insert to array
        if (!empty($questionAnswerCreate)) {
            UserQuestion::create($questionAnswerCreate);
        }
        $this->practice();
    }

    /**
     * Function used to practice questions
     *
     * @return void
     */
    public function practice(): void
    {
        $this->currentUserQuestionList();
        $this->info(" Enter 0 to go to main menu");
        $questionId = (int) $this->askWithValidation("Please select a question id", "question_id", 'required|integer|exists:questions,id', [0]);
        if ($questionId == 0) {
            $this->showMainMenu();
            return;
        }
        $this->practiceQuestion($questionId);
    }

    /**
     * This function will calulate percentage
     *
     * @param  int  $number
     * @param  int  $total
     * @return int
     */
    public function getPercentage(int $number, int $total): int
    {
        // Can't divide by zero so let's catch that early.
        if ($total == 0) {
            return 0;
        }
        return round(($number / $total) * 100);
    }

    /**
     * This function will delete all user practice details, and will show main menu
     *
     * @return void
     */
    public function resetPractice(): void
    {
        UserQuestion::where('user_id', $this->uniqueUserId)->delete();
        $this->showMainMenu();
    }

    /**
     * This function will list all questions
     *
     * @return void
     */
    public function listAllQuestions(): void
    {
        $this->table(
            ['ID', 'Question', 'Answer'],
            Question::all(['id', 'question', 'answer'])->toArray()
        );
        $this->showMainMenu();
    }

    /**
     * This function will ask question to user and validate the inputs
     *
     * @param  string  $text
     * @param  string  $field
     * @param  string  $rule
     * @param  array  $extraValuesToCheck // Array of extra values to match before checking validation rules
     * @return return
     */
    public function askWithValidation(string $text, string $field, string $rule, array $extraValuesToCheck = []): string|null
    {
        $value = $this->ask($text);
        if (in_array($value, $extraValuesToCheck)) {
            return $value;
        }
        while (true) {
            $validator = Validator::make([
                $field => $value
            ], [
                $field => $rule
            ]);
            if ($validator->fails()) {
                $this->error($validator->errors()->first($field));
                $value = $this->ask($text);
            } else {
                break;
            }
        }
        return $value;
    }

    /**
     * This is the function to create a question,
     * Will give an option to enter question and answer
     *
     * @return void
     */
    public function createQuestion(): void
    {
        $question = $this->askWithValidation('Please enter the question ', 'question', 'required|string');
        $answer = $this->askWithValidation('Please enter the answer ', 'answer', 'required|string|max:50');
        Question::create([
            'question' => $question,
            'answer' => $answer
        ]);
        $this->showMainMenu();
    }

    /**
     * Function to list current user answer stats
     *
     * @return void
     */
    public function currentUserQuestionList(): void
    {
        $userQuestionDeatils = Question::with(array('users' => function ($query) {
            $query->where('user_id', $this->uniqueUserId);
        }))->select(['id', 'question'])->get();

        $table = new Table($this->output);
        // Create a new TableSeparator instance.
        $separator = new TableSeparator;

        // Set the table headers.
        $table->setHeaders([
            'ID', 'Question', 'Status'
        ]);
        $totalQuestions = $userQuestionDeatils->count();
        $correctCount = 0;
        foreach ($userQuestionDeatils as $userQuestion) {
            $status = $userQuestion->users[0]->status ?? '';
            if ($status) {
                $correctCount++;
            }
            $table->addRow([$userQuestion->id, $userQuestion->question, UserQuestion::getStatusText($status)]);
        }
        $table->addRow($separator);
        $table->addRow([new TableCell('Percentage of completion', ['colspan' => 2]), $this->getPercentage($correctCount, $totalQuestions)]);
        $table->render();
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Please enter the following:');
        $student_id = $this->ask('Student ID:');

        if($student_id !== FALSE){
            $choice = $this->choice('Report to generate (1 for Diagnostic, 2 for Progress, 3 for Feedback):',[1,2,3], 1);
            $student_json = array_filter(json_decode(file_get_contents('./data/students.json'),true));
            $student_data = collect($student_json)->where("id","=",$student_id)->all();

            $student_response_json = array_filter(json_decode(file_get_contents('./data/student-responses.json'),true));
            $latest_student_response = collect($student_response_json)->where("student.id","=",$student_id)->sortByDesc('completed')->first();
       
            $questions_json = array_filter(json_decode(file_get_contents('./data/questions.json'),true));
            $total_question = count(collect($questions_json));
            $correct_answers_count = 0;
            foreach($latest_student_response['responses'] as $res){
                $question = collect($questions_json)->where('id','=',$res['questionId'])->where('config.key','=',$res['response']);
                if(count($question) > 0){ $correct_answers_count++; };
            }
            if($choice == 1){
                $f_name = $student_data[0]['firstName'];
                $l_name =  $student_data[0]['lastName'];
                $last_assesment_date = $latest_student_response['completed'];
                $this->info($f_name.' '.$l_name.' recently completed Numeracy assessment on '.$last_assesment_date.'
                He got '.$correct_answers_count.' questions right out of '.$total_question.'. Details by strand given below:
                Numeracy and Algebra: 5 out of 5 correct
                Measurement and Geometry: 7 out of 7 correct
                Statistics and Probability: 3 out of 4 correct');

            }
            

        }else{
            $this->info("hello");;
        }
    }
}

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
            $numeracy_total = count(collect($questions_json)->where('strand','=','Number and Algebra'));
            $geometry_total = count(collect($questions_json)->where('strand','=','Measurement and Geometry'));
            $statistics_total = count(collect($questions_json)->where('strand','=','Statistics and Probability'));
            
            $correct_answers_count = 0;
            $numeracy_count = 0;
            $geometry_count = 0;
            $statistics_count = 0;
            foreach($latest_student_response['responses'] as $res){
                $question = collect($questions_json)->where('id','=',$res['questionId'])->where('config.key','=',$res['response']);
                $numeracy_question = collect($questions_json)->where('id','=',$res['questionId'])->where('config.key','=',$res['response'])->where('strand','=','Number and Algebra');
                $geometry_question = collect($questions_json)->where('id','=',$res['questionId'])->where('config.key','=',$res['response'])->where('strand','=','Measurement and Geometry');
                $statistics_question = collect($questions_json)->where('id','=',$res['questionId'])->where('config.key','=',$res['response'])->where('strand','=','Statistics and Probability');
                if(count($question) > 0){ $correct_answers_count++; };
                if(count($numeracy_question) > 0){ $numeracy_count++; };
                if(count($geometry_question) > 0){ $geometry_count++; };
                if(count($statistics_question) > 0){ $statistics_count ++; };
            }
            if($choice == 1){
                $f_name = $student_data[0]['firstName'];
                $l_name =  $student_data[0]['lastName'];
                $last_assesment_date = $latest_student_response['completed'];
                $this->info($f_name.'a '.$l_name.' recently completed Numeracy assessment on '.$last_assesment_date.'
                He got '.$correct_answers_count.' questions right out of '.$total_question.'. Details by strand given below:
                Numeracy and Algebra: '.$numeracy_count.' out of '.$numeracy_total.' correct
                Measurement and Geometry: '.$geometry_count.' out of '.$geometry_total.' correct
                Statistics and Probability: '.$statistics_count.' out of '.$statistics_total.' correct');

            }
            

        }else{
            $this->info("hello");;
        }
    }
}

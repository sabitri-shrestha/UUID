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
            $student_json = array_filter(json_decode(file_get_contents('./data/students.json'),true));
            $student_response_json = array_filter(json_decode(file_get_contents('./data/student-responses.json'),true));
            $student_data = collect($student_json)->where("id","=",$student_id)->all();
            if(!empty($student_data)){
                //get choice for reprts
                $choice = $this->choice('Report to generate (1 for Diagnostic, 2 for Progress, 3 for Feedback):',[1,2,3],1,
                $maxAttempts = null,
                $allowMultipleSelections = false);
              
                $latest_student_response = collect($student_response_json)->where("student.id","=",$student_id)->sortByDesc('completed')->first();
       
        
            $f_name = $student_data[0]['firstName'];
            $l_name =  $student_data[0]['lastName'];

            $last_assesment_date = $latest_student_response['completed'];

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
          
            //Diagnostic report
            if($choice === 1){
               
                $this->info($f_name.' '.$l_name.' recently completed Numeracy assessment on '.date("j\T\H F Y g:i a", strtotime(str_replace('/', '-', $last_assesment_date)))."\n".
                'He got '.$correct_answers_count.' questions right out of '.$total_question.'. Details by strand given below:'."\n\n".
                'Numeracy and Algebra: '.$numeracy_count.' out of '.$numeracy_total.' correct'."\n".
                'Measurement and Geometry: '.$geometry_count.' out of '.$geometry_total.' correct'."\n".
                'Statistics and Probability: '.$statistics_count.' out of '.$statistics_total.' correct');

            }
            $total_numeracy_test = count(collect($student_response_json)->where("student.id","=",$student_id));
            //Progress report
            if($choice === 2){
                $student_response = collect($student_response_json)->where("student.id","=",$student_id)->all();; 
                $this->info($f_name.' '.$l_name.' has completed Numeracy assessment '.$total_numeracy_test.' times in total. Date and raw score given below:'."\n");
        
                foreach($student_response as $response_key => $response_row){
                    if(isset($response_row['completed'])){
                        $this->info('Date: '.date("j\T\H F Y g:i a", strtotime(str_replace('/', '-', $response_row['completed']))).', Raw Score: '.$response_row['results']['rawScore'].' out of '.$total_question);
                    }                
                    
                  }
             
                $latest_raw_score = $latest_student_response['results']['rawScore'];
                $first_student_response = collect($student_response_json)->where("student.id","=",$student_id)->sortByDesc('completed')->last();
                $first_raw_score = $first_student_response['results']['rawScore'];
                $difference_in_score = $latest_raw_score-$first_raw_score; 
                $this->info("\n".'Tony Stark got '.$difference_in_score.' more correct in the recent completed assessment than the oldest');
            }

            //Feedback report
            if($choice === 3){
                $this->info($f_name.' '.$l_name.' recently completed Numeracy assessment on '.date("j\T\H F Y g:i a", strtotime(str_replace('/', '-', $last_assesment_date)))."\n".
                'He got '.$correct_answers_count.' questions right out of '.$total_question.' Feedback for wrong answers given below'."\n");
                $wrong_question_array=[];
                foreach($latest_student_response['responses'] as $res){
                    $question = collect($questions_json)->where('id','=',$res['questionId']);                           
                    if(count($question) > 0){
                       foreach($question as $ques_row){
                           //if wrong question
                            if($ques_row['config']['key'] != $res['response']){
                                $this->info('Question: '.$ques_row['stem']);
                                //find key of your answer option 
                                if(($your_ans_pos = array_search($res['response'], array_column($ques_row['config']['options'], 'id'), true))!== false){
                                    $this->info('Your answer: ');
                                    $this->info($ques_row['config']['options'][$your_ans_pos]['label'].' with value '.$ques_row['config']['options'][$your_ans_pos]['value']);
                                }
                                //find key of right answer option 
                                if(($right_ans_pos = array_search($ques_row['config']['key'], array_column($ques_row['config']['options'], 'id'), true))!== false){     
                                    $this->info($ques_row['config']['options'][$right_ans_pos]['label'].' with value '.$ques_row['config']['options'][$right_ans_pos]['value']."\n".
                                    'Hint: '.$ques_row['config']['hint']);
                                }  
                            }
                        }
                                
                             
                               
                            }
                       }
                 
                }
            }else{
                $this->error("invalid student ID");
            }
            

        }else{
            $this->error("empty student ID");
        }
    }
}

<?php
namespace DORM\API;

use DORM\Database\DBHandler;
use DORM\Includes\ModelList;
use DORM\Config\Config;

final class API {

    protected bool $tokenRequiered;
    protected string $token = '';

    public function __construct( bool $tokenRequiered = false ){
        $this->tokenRequiered = $tokenRequiered;
        $this->token = Config::$tokens;
        $this->request();
    }

    public function request(){
        $request    = json_decode(file_get_contents("php://input"), true);
        $body       = [];
        $errors     = [];

        if( $this->tokenRequiered && !(isset($request['token']) && $request['token'] == $this->token) ){
            $this->response( [], ['Permission denied']);
            return false;
        }

        if ( isset($request['jobs'] ) && is_array($request['jobs']) ){

            $dbHandler      = DBHandler::getInstance();
            $modelList      = new ModelList( $dbHandler->getConnection());
            $solvedStack    = [];

            foreach ($request['jobs'] as $table) {

                if (isset($table['job'])){
                    $modelFromList = $modelList->findModel($table['from']);

                    if( is_array($modelFromList) && $modelFromList ){

                        // Setup/proof
                        $jobname = ucfirst($table['job']);

                        try {
                            if ( !@include_once('Jobs/' . $jobname . '.php')){
                                throw new \Exception ('Job does not exist/implemented');
                            } 

                            $job = (new \ReflectionClass($jobname))->newInstance( $modelFromList, $table, $dbHandler );
                            $job->do( $modelFromList, $table, $dbHandler );

                            if( $job->getJobData() != null ) {
                                $body[$modelFromList['table_name']] = $job->getJobData();
                            }
                            
                            if( $job->getError() != null ){
                                $errors[] = $job->getError();
                            }
                        }
                        catch( \Exception $e) {    
                            $errors[] = array( 'message' =>  $e->getMessage(), 'request' => $table );
                        }

                    } else {
                        $errors[] = array( 'message' => 'can not found a model in the modellist', 'request' => $table );
                    }
                
                } else {
                    $errors[] = array( 'message' => 'missing key: job', 'request' => $table );
                }
            }

        } else {
            $errors[] = array( 'message' => 'no correct request found');
        }

        $this->response( $body, $errors, $request );
    }

    public function response( $body, $errors, $request){
        header('Content-Type: application/json; charset=UTF-8');

        foreach ( Config::$requestHeadersAPI as $value) {
            header( $value );
        }

        $response = [];
        $response['body'] = $body;
        $response['errors'] = $errors;

        print_r( json_encode( $response ) );
    }

}
?>
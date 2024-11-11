<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Token;

class APIController extends Controller
{
    public $username;
    public $password;
    public $url;

    public function __construct()
    {
        $this->username = env('DHIRAAGU_SMS_USERNAME');
        $this->password = env('DHIRAAGU_SMS_PASSWORD');
        $this->url = env('DHIRAAGU_SMS_URL').'/partners/xmlMessage.jsp';
    }

    public function sendSms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to' => ['required'],
            'message' => ['required'],
            'uid' => ['required','exists:tokens'],
            'token' => ['required','exists:tokens'],
        ]);

        if ($validator->fails())
        {
            $response = [
                "code" => 400,
                "msg" => $validator->errors()->first()
            ];
        }
        elseif(!Token::where('uid', $request->input('uid'))->where('status','active')->exists())
        {
            $response = [
                "code" => 401,
                "msg" => "Access Not Authorized"
            ];
        }
        else
        {
            $to = explode(',',$request->input('to'));
            $client = new \Dash8x\DhiraaguSms\DhiraaguSms($this->username, $this->password, $this->url);

            try
            {
               $message = $client->send($to, $request->input('message'));
               $response = [
                    "code" => 200,
                    "msg" => "Message sent successfully"
               ];
            }
            catch(\Throwable $e)
            {
                $response = [
                    "code" => 400,
                    "msg" => "Invalid syntax"
                ];
            }
        }
            

        return response()->json($response,$response["code"]);

    }

    public function checkStatus(Request $request)
    {
        $id = $request->input('id');
        $key = $request->input('key');

        $client = new \Dash8x\DhiraaguSms\DhiraaguSms($this->username, $this->password, $this->url);

        $delivery = $client->delivery(
          $id, 
          $key 
        );
    }

}

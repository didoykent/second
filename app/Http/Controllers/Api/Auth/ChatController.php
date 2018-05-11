<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Tutor;
use App\Message;
use App\Student;

class ChatController extends Controller
{
    public function getFriendLists(Request $request){

$authuser = JWTAuth::toUser(JWTAuth::getToken());



if ($authuser->role == 'tutor'){



$friendslists = Tutor::with('student')->where('tutor_id', $authuser->id)->get();

$friendslists = $friendslists[0]->student;
}

else{

  $friendslists = $authuser->tutor()->get();
}

return response()->json(['friendLists' => $friendslists, 'currentName' => $authuser->en_name, 'currentUserId' => $authuser->id, 'role' => $authuser->role]);


}


public function initializeData(Request $request){

  $data = $request->socketId;

  $authuser = JWTAuth::toUser(JWTAuth::getToken());

  if($authuser->role == 'tutor'){


    $mySocket = Tutor::find($authuser->id);

    if($mySocket->current_conn_id){

    $mySocket->previous_conn_id = $mySocket->current_conn_id;
    $mySocket->current_conn_id = $data;
    }
    else{
      $mySocket->previous_conn_id = $data;
      $mySocket->current_conn_id =  $data;

    }


    $mySocket->save();

  }
  else{

    $mySocket = Student::find($authuser->id);

    if($mySocket->current_conn_id){

      $mySocket->previous_conn_id = $mySocket->current_conn_id;
      $mySocket->current_conn_id = $data;
    }
    else{
      $mySocket->previous_conn_id = $data;
      $mySocket->current_conn_id =  $data;

    }


    $mySocket->save();

  }




  return response()->json(['current' => $mySocket->current_conn_id, 'previous' => $mySocket->previous_conn_id]);
}

public function getCurrentUserId(){

  $authuser = JWTAuth::toUser(JWTAuth::getToken());

  return response()->json(['currentUserId' => $authuser->id, 'role' => $authuser->role]);
}


public function saveMessage(Request $request){

$currentUser = JWTAuth::toUser(JWTAuth::getToken());
$user2 = Student::find($request->secondUser);
$message = $request->message;


if($currentUser->role == 'tutor'){

  Message::create([

    'message' => $request->message,
    'avatar' => 'https://scontent.ficn2-1.fna.fbcdn.net/v/t1.0-1/p160x160/29468236_901369833374211_8734349036217171968_n.jpg?_nc_cat=0&oh=f8f7428a3e9e807d58b3ef91ef215062&oe=5B760837',
    'name' => $currentUser->en_name,
    'tutors_id' => $currentUser->id,
    'student_id' => $user2->id
  ]);
}

else{
  Message::create([

    'message' => $request->message,
    'avatar' => 'https://scontent.ficn2-1.fna.fbcdn.net/v/t1.0-1/p160x160/29468236_901369833374211_8734349036217171968_n.jpg?_nc_cat=0&oh=f8f7428a3e9e807d58b3ef91ef215062&oe=5B760837',
    'name' => $currentUser->en_name,
    'tutors_id' => $user2->id,
    'student_id' => $currentUser->id
  ]);

}






return response()->json($currentUser);


}


public function getMessages(Request $request){


$currentUser = JWTAuth::toUser(JWTAuth::getToken());

$user2 = Student::find($request->secondUser);

if($currentUser->role == 'tutor'){

  $messages = Message::where('tutors_id', $currentUser->id)->where('student_id', $user2->id)->get();
}
else{

    $messages = Message::where('tutors_id', $user2->id)->where('student_id', $currentUser->id)->get();
}

  return response()->json(['messages' => $messages, 'currentUserName' => $currentUser->en_name, 'secondUserName' => $user2->en_name]);
}


public function testResponse(){


  return response()->json('test');
}




}

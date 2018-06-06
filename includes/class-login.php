<?php
class Login {
    public $user;
    
    public function __construct() {
        global $db;

        session_start();
        
        $this->db = $db;
    }

    public function verify_login($post) {
        if ( ! isset( $post['username'] ) || ! isset( $post['password'] ) ) {
            return false;
        }
        
        //Check if user exists
        $user = $this->user_exists($post['username']);
        if($user !== false){
            if(md5($post['password']) == $user->password){
                //set session variable
                $_SESSION['username'] = $post['username'];
                if(isset($post['rememberme'])){
                    $this->rememberme($user);
                }
                return true;
            }
        } 
        return false;

    }

    private function user_exists($where_value, $where_field = 'username'){

        $user = $this->db->get_results("SELECT * FROM users WHERE {$where_field}=:where_value", ['where_value'=>$where_value]);
        if($user === false){
            return false;
        }
        return $user[0];
    }
  
    // check if session is set and 
    // get user's details if it is set and 
    // assign it to $user in class LOGIN
    public function verify_session() {
        
        if(!isset($_SESSION['username'])){
            return false;
        }

        $username = $_SESSION['username'];

        if ( empty( $_SESSION['username'] ) && ! empty( $_COOKIE['rememberme'] ) ) {
            list($selector, $authenticator) = explode(':', $_COOKIE['rememberme']);
            
            $results = $this->db->get_results("SELECT * FROM auth_tokens WHERE selector = :selector", ['selector'=>$selector]);
            $auth_token = $results[0];
            
            if ( hash_equals( $auth_token->token, hash( 'sha256', base64_decode( $authenticator ) ) ) ) {
                $username = $auth_token->username;
                $_SESSION['username'] = $username;
                return true;
            }
        }
        
        $user =  $this->user_exists( $username );
        
        if ( false !== $user ) {
            $this->user = $user;
            
            return true;
        }      
        return false;      
    }

    private function rememberme($user){
        // Create tokens
        $selector = base64_encode(random_bytes(9));
        $authenticator = random_bytes(33);
        
        //Set cookie
        setcookie(
            'rememberme',
             $selector.':'.base64_encode($authenticator),
            time()+864000,
            '/','',true,true
        );

        //Clear old cookies
        $deletion = $this->db->delete('auth_tokens','username',$user->username);

        //Insert auth token into database
        $insert = $this->db->insert('auth_tokens', 
            array(
                'username'=>$user->username,
                'token'=>base64_encode($authenticator),
                'selector'=>$selector,
                'expires'=> date('Y-m-d\TH:i:s', time()+864000)
            )
        );
    }

    public function lost_password($post){
        //Check if email is submitted
        if(empty($post['email'])){
            return array('status'=>0, 'message'=>'Please enter your email');
        }

        //Verify email exists
        if(!$user = $this->user_exists($post['email'], 'email')){
            return array('status'=>0, 'message'=>'That email does not exist in our records');
        }

        //Generate token and url
        $selector = bin2hex(random_bytes(8));
        $authenticator = random_bytes(32);

        //Token that goes into the url is run thru bin2hex()
        $url = sprintf('%sreset.php%s', ABS_URL, http_build_query([
            'selector'=>$selector,
            'token'=>bin2hex($token)
        ]));

         //Set token expiration
         $expires = new DateTime('NOW');
         $expires->add(new DateInterval('PT01H')); // 1 hour

        //Delete old tokens
        $this->db->delete('password_reset', 'email', $user->email);

        /*Insert token to database
          Token that goes into the DB is run thru hash()
        */
        $insertion = $this->db->insert('password_reset', array(
            'email' => $user->email,
            'selector'=> $selector,
            'token'=> hash('sha256', $token),
            'expires'=> $expires->format('U') //unix timestamp
        ));

        //Email reset link
        if($insertion !== false){
            $to = $user->email;
            $subject = 'Password reset link';
            $message = '<p>Here is your link to reset your password</p>'.$url;
            $message .= '<p>If you did not make this request, you can ignore this email.</p>';
            $message .= '<p>Thanks!</p>';
            $headers = "From: " . ADMIN_NAME . " <" . ADMIN_EMAIL . ">\r\n";
            $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
            $headers .= "Content-type: text/html\r\n";
            
            //Send email
            $send_mail = email($to, $subject, $message, $headers);

            if($send_mail !== false){
                
                //To ensure user is logged out while resetting password
                session_destroy();
                return array('status'=>1, 'message'=>'Check your email for the password reset link');
            }
            return array('status'=>0,'message'=>'There was an error in send your password reset link');
        } 
    }

    public function reset_password($post){

        //Check for required fields
        $required = array('selector','validator','password');
        foreach($required as $key){
            if(empty($post[$key])){
                return array('status'=>0, 'message'=> 'There was an error processing your request. Error code: 001');
            }
        }
        extract($post);

        //Fetch results from DB based on selector and expiry time
        $result = $this->db->get_results("SELECT * FROM password_reset WHERE selector = :selector AND expires >= :time", $selector, time());

        if(empty($result)){
            return array('status'=>0, 'message'=> 'There was an error processing your request. Error code: 002');
        }

        $auth_token = $result[0];
        $calc = hash('sha256', hex2bin($validator));

        //Validate tokens
        if(hash_equals($calc, $auth_token->token)){

            //Verifying email again
            $user = $this->user_exists($auth_token->email, 'email');
            if($user == fasle){
                return array('status'=>0, 'message'=> 'There was an error processing your request. Error code: 003');
            }

            //Update password
            $update = $this->db->update("users", 
                    ['password'=>md5($password)], 
                    $user->ID);

            //Delete any existing tokens for the user
            $delete = $this->db->delete('password_reset','email',$user->email);

            if($update == true){
                session_destroy();
                return array('status'=>1, 'message'=>'Password successfully reset.');
            }
        }
        return array('status'=>0, 'message'=> 'There was an error processing your request. Error code: 004');
    }
    
    public function register($post){

        //Check for required fields
        $required = array('username','email','password');
        $message = [];
        $required_fields = '';
        foreach($required as $key){
            if(empty($post[$key])){
                 $required_fields .= $key.', ';
            }
        }
        if($required_fields != ''){
            $required_fields = rtrim($required_fields, ', ');
            $required_fields = '<strong>Please enter your:</strong> '. $required_fields;
            return array('status'=>0, 'message'=>$required_fields);
        }

        //Check if user aleady exists
        if ($this->user_exists($post['username']) == true) {
            return array('status'=>0, 'message'=>'Username already exists');
        }

        //Validate email
        if(FILTER_VAR($post['email'], FILTER_VALIDATE_EMAIL)==false){
            return array('status'=>0, 'message'=>'Invalid email');
        }

        //Insert into DB
        $insert = $this->db->insert('users',  array(
            'username' => $post['username'], 
            //'password'=>password_hash($post['password'], PASSWORD_DEFAULT),
            'email'=>$post['email'],
            'password'=>md5($post['password']),
            'fullname'=>$post['fullname']
            )
        );

        if($insert == true){
            return array('status'=>1,'message'=>'Account successfully created!');
        }
        return array('status'=>0, 'message'=>'An unknown error occoured.');
    }
}

$login = new Login;
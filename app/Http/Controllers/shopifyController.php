<?php

namespace App\Http\Controllers;
use App\AppCustomer;
use Illuminate\Support\Facades\Validator;
use Shopify;
use Illuminate\Http\Request;
class shopifyController extends Controller
{
    protected $shop = "fsnstr.myshopify.com";
    protected $foo;
    protected $scopes = ['read_products','read_themes'];
  
    public function getPermission()
	  {
	  
	    $this->foo = Shopify::make($this->shop, $this->scopes);
	    return $this->foo->redirect();
	  }
  
  public function getResponse(Request $request)
  {

  	
    $code      = $request->code;
    $shop      = $request->shop;
    $hmac      = $request->hmac;
    $timestamp = $request->timestamp;
    $key       = env( 'SHOPIFY_KEY' );
    $secret    = env( 'SHOPIFY_SECRET' );
    

    $shared_secret =  $secret;
    $params        =  $_GET;
    $hmac          =  $hmac;
    $params = array_diff_key($params, array('hmac' => ''));
    ksort($params);
    $computed_hmac = hash_hmac('sha256', http_build_query($params), $shared_secret);

    if (hash_equals($hmac, $computed_hmac)) {
      if(AppCustomer::where('user_shop', $shop)->exists()){

        if ($code)
        {
            $access_token = AppCustomer::where('user_shop', $shop)->value('app_token');

           
            return redirect()->to('/');
        }
        else
        {
            return "wrong";
        }

      }else{
        
        $query = array(
        "client_id"     => $key, // Your API key
        "client_secret" => $secret, // Your app credentials (secret key)
        "code"          => $code, // Grab the access key from the URL
        "Content-type"  => "application/json"
        );

        // Generate access token URL
        $access_token_url = "https://" . $shop . "/admin/oauth/access_token";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $access_token_url);
        curl_setopt($ch, CURLOPT_POST, count($query));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
        $result = curl_exec($ch);
        curl_close($ch);

        $result       = json_decode($result, true);
        $access_token = $result['access_token'];
        //dd('code: '.$code.' shop: '.$shop.' hmac: '.$hmac.' access_token: '.$access_token.' timestamp: '.$timestamp);

        $customer = new AppCustomer();
        $customer->user_code = $code;
        $customer->user_hmac = $hmac;
        $customer->user_shop = $shop;
        $customer->app_token = $access_token;
        $customer->save();

      }

      // end of Token Url

    } else {

      return 'not valid';

    }
    

  }

   public function appIndex(Request $request)
  {
    return 'hgjh';
  }

  public function addRecords(Request $request){

        //return $request->all();


        $validator = Validator::make($request->all(),[
           'user_code' => 'required',
           'user_hmac' => 'required',
           'user_shop' => 'required',
           'app_token' => 'required'
        ]);

        if($validator->fails()){
            return $validator->errors();
        }

        AppCustomer::create($request->all());

        return "success";

  }

}

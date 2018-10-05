<?php
include "Kairos.php";
require __DIR__ . '/vendor/autoload.php';

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;

// set false for production
$pass_signature = true;

// set LINE channel_access_token and channel_secret
$channel_access_token = "WvKXIpOmHlbezecMVoPBfLF1KWe7jDfWI+AtaI3hC5bJJDEZ3G4ZuUyhn8el9qzJvtnSGLQPKq3YTIB6FxG/XlW74smUKrRx9lRN0pA46IBAEENVukkjA2i4TSWfdC5uk0eDgVfH/rUW2tKLgWEnqgdB04t89/1O/w1cDnyilFU=";
$channel_secret = "54047d02af68936009edb1b1b608fd58";

// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);

$configs =  [
    'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);

// buat route untuk url homepage
$app->get('/', function($req, $res)
{
  echo "Silahkan add bot kenali pahlawan dengan ID : @jhj0876h <br>Created by Miky Setiawan 2018";
});

// buat route untuk webhook
$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature, $httpClient)
{
    // get request body and line signature header
    $body        = file_get_contents('php://input');
    $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';

    // log body and signature
    file_put_contents('php://stderr', 'Body: '.$body);

    if($pass_signature === false)
    {
        // is LINE_SIGNATURE exists in request header?
        if(empty($signature)){
            return $response->withStatus(400, 'Signature not set');
        }

        // is this request comes from LINE?
        if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
            return $response->withStatus(400, 'Invalid signature');
        }
    }

		// kode aplikasi nanti disini
		$data = json_decode($body, true);
		if(is_array($data['events'])){
				foreach ($data['events'] as $event)
				{
						if ($event['type'] == 'message')
						{
									 if(
										 $event['source']['type'] == 'group' or
										 $event['source']['type'] == 'room'
									 ){
										 //message from group / room 
										 if($event['message']['type'] == 'image')
										 {
											 $kairos = new Kairos();
											 /*$message  = new TextMessageBuilder($body);
											 $result = $bot->replyMessage($event['replyToken'], $message);
											 return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());*/
											 $image = $bot->getMessageContent($event['message']['id'])->getRawBody();
											 $args = array(
												 'gallery_name' => "pahlawan",
												 'image' => base64_encode($image)
											 );
											 $respon = $kairos->recognize($args);
											 $data_hasil = json_decode($respon, true);
											 if(isset($data_hasil['Errors'])){
												//ERROR CODE : 5002 = no face found
												$message  = new TextMessageBuilder('Mohon Maaf, Bot tidak dapat mendeteksi wajah, silahkan coba kembali. '.$data_hasil['Errors'][0]['ErrCode']);
												$result = $bot->replyMessage($event['replyToken'], $message);
												return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
											}
											 foreach ($data_hasil['images'] as $hasil){
												 if($hasil['transaction']['status'] == "success"){
													if($hasil['candidates'][0]['subject_id'] == "soekarno" || $hasil['candidates'][0]['subject_id'] == "soekarno1"){
														$message  = new TextMessageBuilder("Dr.(H.C.) Ir. H. Soekarno \nMemiliki nama lahir : Koesno Sosrodihardjo) \nLahir di Surabaya, Jawa Timur, 6 Juni 1901 – Meninggal di Jakarta, 21 Juni 1970 pada umur 69 tahun \nMerupakan presiden Indonesia yang pertama menjabat pada periode 1945–1966. \n Ia memainkan peranan penting dalam memerdekakan bangsa Indonesia dari penjajahan Belanda. \nIa adalah Proklamator Kemerdekaan Indonesia (bersama dengan Mohammad Hatta) yang terjadi pada tanggal 17 Agustus 1945. \nSoekarno adalah yang pertama kali mencetuskan konsep mengenai Pancasila sebagai dasar negara Indonesia dan ia sendiri yang menamainya.(WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "kartini"){
														$message  = new TextMessageBuilder("Raden Adjeng Kartini atau sebenarnya lebih tepat disebut Raden Ayu Kartini \nLahir di Jepara, Hindia Belanda, 21 April 1879 - Meninggal di Rembang, Hindia Belanda, 17 September 1904 pada umur 25 tahun \nAdalah seorang tokoh Jawa dan Pahlawan Nasional Indonesia. \nKartini dikenal sebagai pelopor kebangkitan perempuan pribumi. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "soedirman"){
														$message  = new TextMessageBuilder("Jenderal Besar Raden Soedirman \nlahir 24 Januari 1916 – Meninggal 29 Januari 1950 pada umur 34 tahun \nAdalah seorang perwira tinggi Indonesia pada masa Revolusi Nasional Indonesia. \nBeliau menjadi panglima besar Tentara Nasional Indonesia pertama, ia secara luas terus dihormati di Indonesia. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "hatta" || $hasil['candidates'][0]['subject_id'] == "hatta1" || $hasil['candidates'][0]['subject_id'] == "hatta2"){
														$message  = new TextMessageBuilder("Dr.(HC) Drs. H. Mohammad Hatta lahir dengan nama Mohammad Athar, populer sebagai Bung Hatta \nLahir di Fort de Kock (sekarang Bukittinggi, Sumatera Barat), Hindia Belanda, 12 Agustus 1902 – Meninggal di Jakarta, 14 Maret 1980 pada umur 77 tahun) adalah tokoh pejuang, negarawan, ekonom, dan juga Wakil Presiden Indonesia yang pertama. \nIa bersama Soekarno memainkan peranan penting untuk memerdekakan bangsa Indonesia dari penjajahan Belanda sekaligus memproklamirkannya pada 17 Agustus 1945. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "tomo" || $hasil['candidates'][0]['subject_id'] == "tomo1"){
														$message  = new TextMessageBuilder("Sutomo \nLahir di Surabaya, Jawa Timur, 3 Oktober 1920 – Meninggal di Padang Arafah, Arab Saudi, 7 Oktober 1981 pada umur 61 tahun \nLebih dikenal dengan sapaan akrab oleh rakyat sebagai Bung Tomo, adalah pahlawan yang terkenal karena peranannya dalam membangkitkan semangat rakyat untuk melawan kembalinya penjajah Belanda melalui tentara NICA, yang berakhir dengan pertempuran 10 November 1945 yang hingga kini diperingati sebagai Hari Pahlawan. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "ngurahrai" || $hasil['candidates'][0]['subject_id'] == "ngurahrai1"){
														$message  = new TextMessageBuilder("Kolonel TNI Anumerta I Gusti Ngurah Rai \nLahir di Desa Carangsari, Petang, Kabupaten Badung, Bali, Hindia Belanda, 30 Januari 1917 – Meninggal di Marga, Tabanan, Bali, Indonesia, 20 November 1946 pada umur 29 tahun \nAdalah seorang pahlawan Indonesia dari Kabupaten Badung, Bali. \nNgurah Rai memiliki pasukan yang bernama pasukan 'Ciung Wanara' yang melakukan pertempuran terakhir yang dikenal dengan nama Puputan Margarana. (Puputan, dalam bahasa bali, berarti habis-habisan, sedangkan Margarana berarti Pertempuran di Marga. \nMarga adalah sebuah desa ibukota kecamatan di pelosok Kabupaten Tabanan, Bali) Di tempat puputan tersebut lalu didirikan Taman Makam Pahlawan Margarana. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "antasari" || $hasil['candidates'][0]['subject_id'] == "antasari1"){
														$message  = new TextMessageBuilder("Pangeran Antasari \nLahir di Kayu Tangi, Kesultanan Banjar, 1797 atau 1809 – Meninggal di Bayan Begok, Hindia Belanda, 11 Oktober 1862 pada umur 53 tahun \nAdalah seorang Pahlawan Nasional Indonesia. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "cutnyakdien" || $hasil['candidates'][0]['subject_id'] == "cutnyakdien1"){
														$message  = new TextMessageBuilder("Cut Nyak Dhien \nLahir di Lampadang, Kerajaan Aceh, 1848 – Meninggal di Sumedang, Jawa Barat, 6 November 1908 (dimakamkan di Gunung Puyuh, Sumedang) \nAdalah seorang Pahlawan Nasional Indonesia dari Aceh yang berjuang melawan Belanda pada masa Perang Aceh. \nSetelah wilayah VI Mukim diserang, ia mengungsi, sementara suaminya Ibrahim Lamnga bertempur melawan Belanda. Ibrahim Lamnga tewas di Gle Tarum pada tanggal 29 Juni 1878 yang menyebabkan Cut Nyak Dhien sangat marah dan bersumpah hendak menghancurkan Belanda. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "imambonjol" ){
														$message  = new TextMessageBuilder("Tuanku Imam Bonjol yang memiliki nama lahir Muhammad Shahab \nLahir di Bonjol, Pasaman, Sumatera Barat, Indonesia 1772 - Meninggal dalam pengasingan dan dimakamkan di Lotak, Pineleng, Minahasa, 6 November 1864 \nAdalah salah seorang ulama, pemimpin dan pejuang yang berperang melawan Belanda dalam peperangan yang dikenal dengan nama Perang Padri pada tahun 1803-1838. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "teukuumar" || $hasil['candidates'][0]['subject_id'] == "teukuumar1"){
														$message  = new TextMessageBuilder("Teuku Umar \nLahir di Meulaboh, 1854 - Meninggal di Meulaboh, 11 Februari 1899 \nAdalah pahlawan kemerdekaan Indonesia yang berjuang dengan cara berpura-pura bekerjasama dengan Belanda. \nIa melawan Belanda ketika telah mengumpulkan senjata dan uang yang cukup banyak. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "franskaisiepo"){
														$message  = new TextMessageBuilder("Frans Kaisiepo \nLahir di Wardo, Biak, Papua, 10 Oktober 1921 – Meninggal di Jayapura, Papua, 10 April 1979 pada umur 57 tahun \nAdalah pahlawan nasional Indonesia dari Papua. Frans terlibat dalam Konferensi Malino tahun 1946 yang membicarakan mengenai pembentukan Republik Indonesia Serikat sebagai wakil dari Papua. Ia mengusulkan nama Irian, kata dalam bahasa Biak yang berarti tempat yang panas. \nSelain itu, ia juga pernah menjabat sebagai Gubernur Papua antara tahun 1964-1973. Ia dimakamkan di Taman Makam Pahlawan Cendrawasih, Jayapura. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "juandakartawijaya" || $hasil['candidates'][0]['subject_id'] == "juandakartawijaya1" || $hasil['candidates'][0]['subject_id'] == "juandakartawijaya2"){
														$message  = new TextMessageBuilder("Ir. Haji Raden Djoeanda Kartawidjaja \nLahir di Tasikmalaya, Hindia Belanda, 14 Januari 1911 – Meninggal di Jakarta, 7 November 1963 pada umur 52 tahun \nAdalah Perdana Menteri Indonesia ke-10 sekaligus yang terakhir. Ia menjabat dari 9 April 1957 hingga 9 Juli 1959. Setelah itu ia menjabat sebagai Menteri Keuangan dalam Kabinet Kerja I. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "patimura" || $hasil['candidates'][0]['subject_id'] == "patimura1" || $hasil['candidates'][0]['subject_id'] == "patimura2"){
														$message  = new TextMessageBuilder("Pattimura(atau Thomas Matulessy) \nLahir di Haria, pulau Saparua, Maluku, 8 Juni 1783 – Meninggal di Ambon, Maluku, 16 Desember 1817 pada umur 34 tahun /nDikenal dengan nama Kapitan Pattimura adalah pahlawan Maluku dan merupakan Pahlawan nasional Indonesia. \nMenurut buku biografi Pattimura versi pemerintah yang pertama kali terbit, M Sapija menulis bahwa pahlawan Pattimura tergolong turunan bangsawan dan berasal dari Nusa Ina (Seram). Ayahnya yang bernama Antoni Mattulessy adalah anak dari Kasimiliali Pattimura Mattulessy. Yang terakhir ini adalah putra raja Sahulau. Sahulau merupakan nama orang di negeri yang terletak dalam sebuah teluk di Seram. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "sultanmahmud" || $hasil['candidates'][0]['subject_id'] == "sultanmahmud1" ){
														$message  = new TextMessageBuilder("Sultan Mahmud Badaruddin II \nLahir di Palembang, 1767 Meningggal di Ternate, 26 September 1852) \nAdalah pemimpin kesultanan Palembang-Darussalam selama dua periode (1803-1813, 1818-1821), setelah masa pemerintahan ayahnya, Sultan Muhammad Bahauddin (1776-1803). Nama aslinya sebelum menjadi Sultan adalah Raden Hasan Pangeran Ratu. \nDalam masa pemerintahannya, ia beberapa kali memimpin pertempuran melawan Inggris dan Belanda, di antaranya yang disebut Perang Menteng. Pada tangga 14 Juli 1821, ketika Belanda berhasil menguasai Palembang, Sultan Mahmud Badaruddin II dan keluarga ditangkap dan diasingkan ke Ternate. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "otto" || $hasil['candidates'][0]['subject_id'] == "otto1" || $hasil['candidates'][0]['subject_id'] == "otto2"){
														$message  = new TextMessageBuilder("Raden Otto Iskandardinata \nLahir di Bandung, Jawa Barat, 31 Maret 1897 – Meninggal di Mauk, Tangerang, Banten, 20 Desember 1945 pada umur 48 tahun \nAdalah salah satu Pahlawan Nasional Indonesia. Ia mendapat nama julukan si Jalak Harupat. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "husnithamrin" || $hasil['candidates'][0]['subject_id'] == "husnithamrin1" || $hasil['candidates'][0]['subject_id'] == "husnithamrin2"){
														$message  = new TextMessageBuilder("Mohammad Husni Thamrin \nLahir di Weltevreden, Batavia, 16 Februari 1894 – Meninggal di Senen, Batavia, 11 Januari 1941 pada umur 46 tahun \nAdalah seorang politisi era Hindia Belanda yang kemudian dianugerahi gelar pahlawan nasional Indonesia. \nMunculnya  Muhammad Husni  Thamrin sebagai  tokoh  pergerakan  yang  berkaliber  nasional  tidaklah  tidak  mudah.  Untuk mencapai  tingkat  itu  ia  memulai  dari  bawah,  dari  tingkat lokal. Dia memulai geraknya  sebagai  seorang  tokoh  (lokal)  Betawi.  (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "cutmeutia" || $hasil['candidates'][0]['subject_id'] == "cutmeutia1"){
														$message  = new TextMessageBuilder("Tjoet Nyak Meutia atau Cut Nyak Meutia \n Lahir di Keureutoe, Pirak, Aceh Utara, 1870 – Meninggal di Alue Kurieng, Aceh, 24 Oktober 1910 \nAdalah pahlawan nasional Indonesia dari daerah Aceh. Ia dimakamkan di Alue Kurieng, Aceh. \nAwalnya Tjoet Meutia melakukan perlawanan terhadap Belanda bersama suaminya Teuku Muhammad atau Teuku Tjik Tunong. Namun pada bulan Maret 1905, Tjik Tunong berhasil ditangkap Belanda dan dihukum mati di tepi pantai Lhokseumawe. Sebelum meninggal, Teuku Tjik Tunong berpesan kepada sahabatnya Pang Nagroe agar mau menikahi istrinya dan merawat anaknya Teuku Raja Sabi. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "cokroaminoto"){
														$message  = new TextMessageBuilder("Raden Hadji Oemar Said Tjokroaminoto \nLahir di Ponorogo, Jawa Timur, 16 Agustus 1882 – Meninggal di Yogyakarta, Indonesia, 17 Desember 1934 pada umur 52 tahun( dalam Buku Sejarah Sarekat Islam dan Pendidikan Bangsa, karangan Drs. Mansur, MA. Penerbit Pustaka Pelajar, 2004; halaman 13) \nBeliau lebih dikenal dengan nama H.O.S Cokroaminoto, merupakan salah satu pemimpin organisasi pertama di Indonesia, yaitu Sarekat Islam (SI). (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "diponegoro" || $hasil['candidates'][0]['subject_id'] == "diponegoro1"){
														$message  = new TextMessageBuilder("Bendara Pangeran Harya Dipanegara (lebih dikenal dengan nama Pangeran Diponegoro) \nLahir di Ngayogyakarta Hadiningrat, 11 November 1785 – Meninggal di Makassar, Hindia Belanda, 8 Januari 1855 pada umur 69 tahun \nPangeran Diponegoro adalah putra sulung dari Sultan Hamengkubuwana III, raja ketiga di Kesultanan Yogyakarta. Pangeran Diponegoro terkenal karena memimpin Perang Diponegoro/Perang Jawa (1825-1830) melawan pemerintah Hindia Belanda. Perang tersebut tercatat sebagai perang dengan korban paling besar dalam sejarah Indonesia. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "fatmawati"){
														$message  = new TextMessageBuilder("Fatmawati yang bernama asli Fatimah \nLahir di Bengkulu, 5 Februari 1923 – Meninggal di Kuala Lumpur, Malaysia, 14 Mei 1980 pada umur 57 tahun \nAdalah istri dari Presiden Indonesia pertama Soekarno. Ia menjadi Ibu Negara Indonesia pertama dari tahun 1945 hingga tahun 1967 dan merupakan istri ke-3 dari Presiden Pertama Indonesia, Soekarno. Ia juga dikenal akan jasanya dalam menjahit Bendera Pusaka Sang Saka Merah Putih yang turut dikibarkan pada upacara Proklamasi Kemerdekaan Indonesia di Jakarta pada tanggal 17 Agustus 1945. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}
													 
												 }else{
													$message  = new TextMessageBuilder('Maaf, Karena terbatasnya waktu dan biaya, kemungkinan foto pahlawan yang anda kirim belum kami masukkan. Bot tidak dapat mengenali foto ini, silahkan coba lagi atau ketik "help" untuk melihat nama pahlawan yang sudah ada di server');
													 $result = $bot->replyMessage($event['replyToken'], $message);
													 return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
												 }
											 }
 
										 
										 }else{
											 // selain image
											 if($event['source']['userId']){

														$userId     = $event['source']['userId'];
														$getprofile = $bot->getProfile($userId);
														$profile    = $getprofile->getJSONDecodedBody();
														$greetings  = new TextMessageBuilder("Halo ".$profile['displayName']."\nSilahkan upload foto pahlawan dan dapatkan informasi mengenai pahlawan tersebut");

														$result = $bot->replyMessage($event['replyToken'], $greetings);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());

												} else {
													if(strtolower($event['message']['text'] ) == 'help'){
														$message  = new TextMessageBuilder("Nama Pahlawan yang sudah terdaftar di bot ini: 
		Ir. Soekarno 
		Mohammad Hatta 
		RA Kartini  
		Jenderal Soedirman  
		Bung Tomo  
		I Gusti Ngurah Rai  
		Pangeran Antasari  
		Cut Nyak Dien  
		Tuanku Imam Bonjol  
		Teuku Umar  
		Frans Kaisiepo  
		Djuanda Kartawijaya  
		Kapitan Pattimura  
		Sultan Mahmud Badaruddin  
		Otto Iskandardinata  
		Mohammad Husni Thamrin  
		Cut Nyak Meutia 
		HOS Cokroaminoto 
		Pangeran Diponegoro 
		Fatmawati  
		\n\nDibuat oleh : Miky Setiawan (2018) \nTerimakasih telah menggunakan bot ini");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													 }else{
														$message  = new TextMessageBuilder("Silahkan upload foto pahlawan dan dapatkan informasi mengenai pahlawan tersebut");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													 }
									 		}
									}} else {
										//message from single user
										if($event['message']['type'] == 'image')
										{
											$kairos = new Kairos();
											/*$message  = new TextMessageBuilder($body);
											$result = $bot->replyMessage($event['replyToken'], $message);
											return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());*/
											$image = $bot->getMessageContent($event['message']['id'])->getRawBody();
											$args = array(
												'gallery_name' => "pahlawan",
												'image' => base64_encode($image)
											);
											$respon = $kairos->recognize($args);
											$data_hasil = json_decode($respon, true);
											if(isset($data_hasil['Errors'])){
												//ERROR CODE : 5002 = no face found
												$message  = new TextMessageBuilder('Mohon Maaf, Bot tidak dapat mendeteksi wajah, silahkan coba kembali. '.$data_hasil['Errors'][0]['ErrCode']);
												$result = $bot->replyMessage($event['replyToken'], $message);
												return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
											}
											foreach ($data_hasil['images'] as $hasil){
												if($hasil['transaction']['status'] == "success"){
													if($hasil['candidates'][0]['subject_id'] == "soekarno" || $hasil['candidates'][0]['subject_id'] == "soekarno1"){
														$message  = new TextMessageBuilder("Dr.(H.C.) Ir. H. Soekarno \nMemiliki nama lahir : Koesno Sosrodihardjo) \nLahir di Surabaya, Jawa Timur, 6 Juni 1901 – Meninggal di Jakarta, 21 Juni 1970 pada umur 69 tahun \nMerupakan presiden Indonesia yang pertama menjabat pada periode 1945–1966. \n Ia memainkan peranan penting dalam memerdekakan bangsa Indonesia dari penjajahan Belanda. \nIa adalah Proklamator Kemerdekaan Indonesia (bersama dengan Mohammad Hatta) yang terjadi pada tanggal 17 Agustus 1945. \nSoekarno adalah yang pertama kali mencetuskan konsep mengenai Pancasila sebagai dasar negara Indonesia dan ia sendiri yang menamainya.(WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "kartini"){
														$message  = new TextMessageBuilder("Raden Adjeng Kartini atau sebenarnya lebih tepat disebut Raden Ayu Kartini \nLahir di Jepara, Hindia Belanda, 21 April 1879 - Meninggal di Rembang, Hindia Belanda, 17 September 1904 pada umur 25 tahun \nAdalah seorang tokoh Jawa dan Pahlawan Nasional Indonesia. \nKartini dikenal sebagai pelopor kebangkitan perempuan pribumi. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "soedirman"){
														$message  = new TextMessageBuilder("Jenderal Besar Raden Soedirman \nlahir 24 Januari 1916 – Meninggal 29 Januari 1950 pada umur 34 tahun \nAdalah seorang perwira tinggi Indonesia pada masa Revolusi Nasional Indonesia. \nBeliau menjadi panglima besar Tentara Nasional Indonesia pertama, ia secara luas terus dihormati di Indonesia. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "hatta" || $hasil['candidates'][0]['subject_id'] == "hatta1" || $hasil['candidates'][0]['subject_id'] == "hatta2"){
														$message  = new TextMessageBuilder("Dr.(HC) Drs. H. Mohammad Hatta lahir dengan nama Mohammad Athar, populer sebagai Bung Hatta \nLahir di Fort de Kock (sekarang Bukittinggi, Sumatera Barat), Hindia Belanda, 12 Agustus 1902 – Meninggal di Jakarta, 14 Maret 1980 pada umur 77 tahun) adalah tokoh pejuang, negarawan, ekonom, dan juga Wakil Presiden Indonesia yang pertama. \nIa bersama Soekarno memainkan peranan penting untuk memerdekakan bangsa Indonesia dari penjajahan Belanda sekaligus memproklamirkannya pada 17 Agustus 1945. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "tomo" || $hasil['candidates'][0]['subject_id'] == "tomo1"){
														$message  = new TextMessageBuilder("Sutomo \nLahir di Surabaya, Jawa Timur, 3 Oktober 1920 – Meninggal di Padang Arafah, Arab Saudi, 7 Oktober 1981 pada umur 61 tahun \nLebih dikenal dengan sapaan akrab oleh rakyat sebagai Bung Tomo, adalah pahlawan yang terkenal karena peranannya dalam membangkitkan semangat rakyat untuk melawan kembalinya penjajah Belanda melalui tentara NICA, yang berakhir dengan pertempuran 10 November 1945 yang hingga kini diperingati sebagai Hari Pahlawan. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "ngurahrai" || $hasil['candidates'][0]['subject_id'] == "ngurahrai1"){
														$flexTemplate = file_get_contents("flex_message.json"); // template flex message
														//Editing template
														$data = json_decode($flexTemplate, true);
														$data['body']['content'][0]['text'] = "TEST";
														$newJsonString = json_encode($data);


														$result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
															'replyToken' => $event['replyToken'],
															'messages'   => [
																[
																	'type'     => 'flex',
																	'altText'  => 'Info Pahlawan',
																	'contents' => json_decode($newJsonString)
																]
															],
														]);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "antasari" || $hasil['candidates'][0]['subject_id'] == "antasari1"){
														$message  = new TextMessageBuilder("Pangeran Antasari \nLahir di Kayu Tangi, Kesultanan Banjar, 1797 atau 1809 – Meninggal di Bayan Begok, Hindia Belanda, 11 Oktober 1862 pada umur 53 tahun \nAdalah seorang Pahlawan Nasional Indonesia. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "cutnyakdien" || $hasil['candidates'][0]['subject_id'] == "cutnyakdien1"){
														$message  = new TextMessageBuilder("Cut Nyak Dhien \nLahir di Lampadang, Kerajaan Aceh, 1848 – Meninggal di Sumedang, Jawa Barat, 6 November 1908 (dimakamkan di Gunung Puyuh, Sumedang) \nAdalah seorang Pahlawan Nasional Indonesia dari Aceh yang berjuang melawan Belanda pada masa Perang Aceh. \nSetelah wilayah VI Mukim diserang, ia mengungsi, sementara suaminya Ibrahim Lamnga bertempur melawan Belanda. Ibrahim Lamnga tewas di Gle Tarum pada tanggal 29 Juni 1878 yang menyebabkan Cut Nyak Dhien sangat marah dan bersumpah hendak menghancurkan Belanda. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "imambonjol" ){
														$message  = new TextMessageBuilder("Tuanku Imam Bonjol yang memiliki nama lahir Muhammad Shahab \nLahir di Bonjol, Pasaman, Sumatera Barat, Indonesia 1772 - Meninggal dalam pengasingan dan dimakamkan di Lotak, Pineleng, Minahasa, 6 November 1864 \nAdalah salah seorang ulama, pemimpin dan pejuang yang berperang melawan Belanda dalam peperangan yang dikenal dengan nama Perang Padri pada tahun 1803-1838. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "teukuumar" || $hasil['candidates'][0]['subject_id'] == "teukuumar1"){
														$message  = new TextMessageBuilder("Teuku Umar \nLahir di Meulaboh, 1854 - Meninggal di Meulaboh, 11 Februari 1899 \nAdalah pahlawan kemerdekaan Indonesia yang berjuang dengan cara berpura-pura bekerjasama dengan Belanda. \nIa melawan Belanda ketika telah mengumpulkan senjata dan uang yang cukup banyak. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "franskaisiepo"){
														$message  = new TextMessageBuilder("Frans Kaisiepo \nLahir di Wardo, Biak, Papua, 10 Oktober 1921 – Meninggal di Jayapura, Papua, 10 April 1979 pada umur 57 tahun \nAdalah pahlawan nasional Indonesia dari Papua. Frans terlibat dalam Konferensi Malino tahun 1946 yang membicarakan mengenai pembentukan Republik Indonesia Serikat sebagai wakil dari Papua. Ia mengusulkan nama Irian, kata dalam bahasa Biak yang berarti tempat yang panas. \nSelain itu, ia juga pernah menjabat sebagai Gubernur Papua antara tahun 1964-1973. Ia dimakamkan di Taman Makam Pahlawan Cendrawasih, Jayapura. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "juandakartawijaya" || $hasil['candidates'][0]['subject_id'] == "juandakartawijaya1" || $hasil['candidates'][0]['subject_id'] == "juandakartawijaya2"){
														$message  = new TextMessageBuilder("Ir. Haji Raden Djoeanda Kartawidjaja \nLahir di Tasikmalaya, Hindia Belanda, 14 Januari 1911 – Meninggal di Jakarta, 7 November 1963 pada umur 52 tahun \nAdalah Perdana Menteri Indonesia ke-10 sekaligus yang terakhir. Ia menjabat dari 9 April 1957 hingga 9 Juli 1959. Setelah itu ia menjabat sebagai Menteri Keuangan dalam Kabinet Kerja I. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "patimura" || $hasil['candidates'][0]['subject_id'] == "patimura1" || $hasil['candidates'][0]['subject_id'] == "patimura2"){
														$message  = new TextMessageBuilder("Pattimura(atau Thomas Matulessy) \nLahir di Haria, pulau Saparua, Maluku, 8 Juni 1783 – Meninggal di Ambon, Maluku, 16 Desember 1817 pada umur 34 tahun /nDikenal dengan nama Kapitan Pattimura adalah pahlawan Maluku dan merupakan Pahlawan nasional Indonesia. \nMenurut buku biografi Pattimura versi pemerintah yang pertama kali terbit, M Sapija menulis bahwa pahlawan Pattimura tergolong turunan bangsawan dan berasal dari Nusa Ina (Seram). Ayahnya yang bernama Antoni Mattulessy adalah anak dari Kasimiliali Pattimura Mattulessy. Yang terakhir ini adalah putra raja Sahulau. Sahulau merupakan nama orang di negeri yang terletak dalam sebuah teluk di Seram. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "sultanmahmud" || $hasil['candidates'][0]['subject_id'] == "sultanmahmud1" ){
														$message  = new TextMessageBuilder("Sultan Mahmud Badaruddin II \nLahir di Palembang, 1767 Meningggal di Ternate, 26 September 1852) \nAdalah pemimpin kesultanan Palembang-Darussalam selama dua periode (1803-1813, 1818-1821), setelah masa pemerintahan ayahnya, Sultan Muhammad Bahauddin (1776-1803). Nama aslinya sebelum menjadi Sultan adalah Raden Hasan Pangeran Ratu. \nDalam masa pemerintahannya, ia beberapa kali memimpin pertempuran melawan Inggris dan Belanda, di antaranya yang disebut Perang Menteng. Pada tangga 14 Juli 1821, ketika Belanda berhasil menguasai Palembang, Sultan Mahmud Badaruddin II dan keluarga ditangkap dan diasingkan ke Ternate. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "otto" || $hasil['candidates'][0]['subject_id'] == "otto1" || $hasil['candidates'][0]['subject_id'] == "otto2"){
														$message  = new TextMessageBuilder("Raden Otto Iskandardinata \nLahir di Bandung, Jawa Barat, 31 Maret 1897 – Meninggal di Mauk, Tangerang, Banten, 20 Desember 1945 pada umur 48 tahun \nAdalah salah satu Pahlawan Nasional Indonesia. Ia mendapat nama julukan si Jalak Harupat. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "husnithamrin" || $hasil['candidates'][0]['subject_id'] == "husnithamrin1" || $hasil['candidates'][0]['subject_id'] == "husnithamrin2"){
														$message  = new TextMessageBuilder("Mohammad Husni Thamrin \nLahir di Weltevreden, Batavia, 16 Februari 1894 – Meninggal di Senen, Batavia, 11 Januari 1941 pada umur 46 tahun \nAdalah seorang politisi era Hindia Belanda yang kemudian dianugerahi gelar pahlawan nasional Indonesia. \nMunculnya  Muhammad Husni  Thamrin sebagai  tokoh  pergerakan  yang  berkaliber  nasional  tidaklah  tidak  mudah.  Untuk mencapai  tingkat  itu  ia  memulai  dari  bawah,  dari  tingkat lokal. Dia memulai geraknya  sebagai  seorang  tokoh  (lokal)  Betawi.  (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "cutmeutia" || $hasil['candidates'][0]['subject_id'] == "cutmeutia1"){
														$message  = new TextMessageBuilder("Tjoet Nyak Meutia atau Cut Nyak Meutia \n Lahir di Keureutoe, Pirak, Aceh Utara, 1870 – Meninggal di Alue Kurieng, Aceh, 24 Oktober 1910 \nAdalah pahlawan nasional Indonesia dari daerah Aceh. Ia dimakamkan di Alue Kurieng, Aceh. \nAwalnya Tjoet Meutia melakukan perlawanan terhadap Belanda bersama suaminya Teuku Muhammad atau Teuku Tjik Tunong. Namun pada bulan Maret 1905, Tjik Tunong berhasil ditangkap Belanda dan dihukum mati di tepi pantai Lhokseumawe. Sebelum meninggal, Teuku Tjik Tunong berpesan kepada sahabatnya Pang Nagroe agar mau menikahi istrinya dan merawat anaknya Teuku Raja Sabi. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "cokroaminoto"){
														$message  = new TextMessageBuilder("Raden Hadji Oemar Said Tjokroaminoto \nLahir di Ponorogo, Jawa Timur, 16 Agustus 1882 – Meninggal di Yogyakarta, Indonesia, 17 Desember 1934 pada umur 52 tahun( dalam Buku Sejarah Sarekat Islam dan Pendidikan Bangsa, karangan Drs. Mansur, MA. Penerbit Pustaka Pelajar, 2004; halaman 13) \nBeliau lebih dikenal dengan nama H.O.S Cokroaminoto, merupakan salah satu pemimpin organisasi pertama di Indonesia, yaitu Sarekat Islam (SI). (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "diponegoro" || $hasil['candidates'][0]['subject_id'] == "diponegoro1"){
														$message  = new TextMessageBuilder("Bendara Pangeran Harya Dipanegara (lebih dikenal dengan nama Pangeran Diponegoro) \nLahir di Ngayogyakarta Hadiningrat, 11 November 1785 – Meninggal di Makassar, Hindia Belanda, 8 Januari 1855 pada umur 69 tahun \nPangeran Diponegoro adalah putra sulung dari Sultan Hamengkubuwana III, raja ketiga di Kesultanan Yogyakarta. Pangeran Diponegoro terkenal karena memimpin Perang Diponegoro/Perang Jawa (1825-1830) melawan pemerintah Hindia Belanda. Perang tersebut tercatat sebagai perang dengan korban paling besar dalam sejarah Indonesia. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}else if($hasil['candidates'][0]['subject_id'] == "fatmawati"){
														$message  = new TextMessageBuilder("Fatmawati yang bernama asli Fatimah \nLahir di Bengkulu, 5 Februari 1923 – Meninggal di Kuala Lumpur, Malaysia, 14 Mei 1980 pada umur 57 tahun \nAdalah istri dari Presiden Indonesia pertama Soekarno. Ia menjadi Ibu Negara Indonesia pertama dari tahun 1945 hingga tahun 1967 dan merupakan istri ke-3 dari Presiden Pertama Indonesia, Soekarno. Ia juga dikenal akan jasanya dalam menjahit Bendera Pusaka Sang Saka Merah Putih yang turut dikibarkan pada upacara Proklamasi Kemerdekaan Indonesia di Jakarta pada tanggal 17 Agustus 1945. (WIKIPEDIA)");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													}
													
												}else{
													$message  = new TextMessageBuilder('Maaf, Karena terbatasnya waktu dan biaya, kemungkinan foto pahlawan yang anda kirim belum kami masukkan. Bot tidak dapat mengenali foto ini, silahkan coba lagi atau ketik "help" untuk melihat nama pahlawan yang sudah ada di server');
													$result = $bot->replyMessage($event['replyToken'], $message);
													return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
												}
											}

										
										}else{
										 // selain image
										 if(strtolower($event['message']['text'] ) == 'help'){
											$message  = new TextMessageBuilder("Nama Pahlawan yang sudah terdaftar di bot ini: 
Ir. Soekarno
Mohammad Hatta
RA Kartini
Jenderal Soedirman
Bung Tomo 
I Gusti Ngurah Rai  
Pangeran Antasari  
Cut Nyak Dien  
Tuanku Imam Bonjol  
Teuku Umar  
Frans Kaisiepo  
Djuanda Kartawijaya  
Kapitan Pattimura  
Sultan Mahmud Badaruddin  
Otto Iskandardinata  
Mohammad Husni Thamrin  
Cut Nyak Meutia 
HOS Cokroaminoto 
Pangeran Diponegoro 
Fatmawati  
\n\nDibuat oleh : Miky Setiawan (2018) \nTerimakasih telah menggunakan bot ini");
											$result = $bot->replyMessage($event['replyToken'], $message);
											return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
										 }else{
											$message  = new TextMessageBuilder("Silahkan upload foto pahlawan dan dapatkan informasi mengenai pahlawan tersebut");
											$result = $bot->replyMessage($event['replyToken'], $message);
											return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
										 }
										}
									 }
						}
				}
		}

});

$app->run();
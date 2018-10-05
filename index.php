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
													$flexTemplate = file_get_contents("flex_message.json"); // template flex message
													//Editing template
													$data = json_decode($flexTemplate, true);

													if($hasil['candidates'][0]['subject_id'] == "soekarno" || $hasil['candidates'][0]['subject_id'] == "soekarno1"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/0/01/Presiden_Sukarno.jpg/220px-Presiden_Sukarno.jpg";
														$data['body']['contents'][0]['text'] = "Dr.(H.C.) Ir. H. Soekarno";
														$data['body']['contents'][1]['text'] = "Memiliki nama lahir : Koesno Sosrodihardjo \nLahir di Surabaya, Jawa Timur, 6 Juni 1901 – Meninggal di Jakarta, 21 Juni 1970 pada umur 69 tahun \nMerupakan presiden Indonesia yang pertama menjabat pada periode 1945–1966. \n\nIa memainkan peranan penting dalam memerdekakan bangsa Indonesia dari penjajahan Belanda. \nIa adalah Proklamator Kemerdekaan Indonesia (bersama dengan Mohammad Hatta) yang terjadi pada tanggal 17 Agustus 1945. \n\nSoekarno adalah yang pertama kali mencetuskan konsep mengenai Pancasila sebagai dasar negara Indonesia dan ia sendiri yang menamainya.(WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Ir+Soekarno";
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
													}else if($hasil['candidates'][0]['subject_id'] == "kartini"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/2/23/COLLECTIE_TROPENMUSEUM_Portret_van_Raden_Ajeng_Kartini_TMnr_10018776.jpg/200px-COLLECTIE_TROPENMUSEUM_Portret_van_Raden_Ajeng_Kartini_TMnr_10018776.jpg";
														$data['body']['contents'][0]['text'] = "Raden Adjeng Kartini";
														$data['body']['contents'][1]['text'] = "Lahir di Jepara, Hindia Belanda, 21 April 1879 - Meninggal di Rembang, Hindia Belanda, 17 September 1904 pada umur 25 tahun \n\nAdalah seorang tokoh Jawa dan Pahlawan Nasional Indonesia. \nKartini dikenal sebagai pelopor kebangkitan perempuan pribumi. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Raden+Adjeng+Kartini";
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
													}else if($hasil['candidates'][0]['subject_id'] == "soedirman"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Sudirman.jpg/220px-Sudirman.jpg";
														$data['body']['contents'][0]['text'] = "Jenderal Besar Raden Soedirman";
														$data['body']['contents'][1]['text'] = "Lahir 24 Januari 1916 – Meninggal 29 Januari 1950 pada umur 34 tahun \n\nAdalah seorang perwira tinggi Indonesia pada masa Revolusi Nasional Indonesia. \nBeliau menjadi panglima besar Tentara Nasional Indonesia pertama, ia secara luas terus dihormati di Indonesia. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Jendral+Soedirman";
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
													}else if($hasil['candidates'][0]['subject_id'] == "hatta" || $hasil['candidates'][0]['subject_id'] == "hatta1" || $hasil['candidates'][0]['subject_id'] == "hatta2"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/f/ff/Mohammad_Hatta_1950.jpg/220px-Mohammad_Hatta_1950.jpg";
														$data['body']['contents'][0]['text'] = "Dr.(HC) Drs. H. Mohammad Hatta";
														$data['body']['contents'][1]['text'] = "Lahir di Fort de Kock (sekarang Bukittinggi, Sumatera Barat), Hindia Belanda, 12 Agustus 1902 – Meninggal di Jakarta, 14 Maret 1980 pada umur 77 tahun. \n\nAdalah tokoh pejuang, negarawan, ekonom, dan juga Wakil Presiden Indonesia yang pertama. \nIa bersama Soekarno memainkan peranan penting untuk memerdekakan bangsa Indonesia dari penjajahan Belanda sekaligus memproklamirkannya pada 17 Agustus 1945. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Mohammad+Hatta";
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
													}else if($hasil['candidates'][0]['subject_id'] == "tomo" || $hasil['candidates'][0]['subject_id'] == "tomo1"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/e/ed/Bung_Tomo.jpg/220px-Bung_Tomo.jpg";
														$data['body']['contents'][0]['text'] = "Sutomo";
														$data['body']['contents'][1]['text'] = "Lahir di Surabaya, Jawa Timur, 3 Oktober 1920 – Meninggal di Padang Arafah, Arab Saudi, 7 Oktober 1981 pada umur 61 tahun \n\nLebih dikenal dengan sapaan akrab oleh rakyat sebagai Bung Tomo, adalah pahlawan yang terkenal karena peranannya dalam membangkitkan semangat rakyat untuk melawan kembalinya penjajah Belanda melalui tentara NICA, yang berakhir dengan pertempuran 10 November 1945 yang hingga kini diperingati sebagai Hari Pahlawan. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Sutomo";
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
													}else if($hasil['candidates'][0]['subject_id'] == "ngurahrai" || $hasil['candidates'][0]['subject_id'] == "ngurahrai1"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/id/thumb/f/ff/Gusti_ngurah_rai.jpg/220px-Gusti_ngurah_rai.jpg";
														$data['body']['contents'][0]['text'] = "Kolonel TNI Anumerta I Gusti Ngurah Rai";
														$data['body']['contents'][1]['text'] = "Lahir di Desa Carangsari, Petang, Kabupaten Badung, Bali, Hindia Belanda, 30 Januari 1917 – Meninggal di Marga, Tabanan, Bali, Indonesia, 20 November 1946 pada umur 29 tahun \n\nAdalah seorang pahlawan Indonesia dari Kabupaten Badung, Bali. \nNgurah Rai memiliki pasukan yang bernama pasukan 'Ciung Wanara' yang melakukan pertempuran terakhir yang dikenal dengan nama Puputan Margarana. (Puputan, dalam bahasa bali, berarti habis-habisan, sedangkan Margarana berarti Pertempuran di Marga. \nMarga adalah sebuah desa ibukota kecamatan di pelosok Kabupaten Tabanan, Bali) Di tempat puputan tersebut lalu didirikan Taman Makam Pahlawan Margarana. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=I+Gusti+Ngurah+Rai";
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
													}else if($hasil['candidates'][0]['subject_id'] == "antasari" || $hasil['candidates'][0]['subject_id'] == "antasari1"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/id/thumb/c/c8/Pangeran_Antasari_Museum_Lambung_Mangkurat.JPG/220px-Pangeran_Antasari_Museum_Lambung_Mangkurat.JPG";
														$data['body']['contents'][0]['text'] = "Pangeran Antasari";
														$data['body']['contents'][1]['text'] = "Lahir di Kayu Tangi, Kesultanan Banjar, 1797 atau 1809 – Meninggal di Bayan Begok, Hindia Belanda, 11 Oktober 1862 pada umur 53 tahun \n\nAdalah seorang Pahlawan Nasional Indonesia.\nIa adalah Sultan Banjar.\nPada 14 Maret 1862, dia dinobatkan sebagai pimpinan pemerintahan tertinggi di Kesultanan Banjar (Sultan Banjar) dengan menyandang gelar Panembahan Amiruddin Khalifatul Mukminin dihadapan para kepala suku Dayak dan adipati (gubernur) penguasa wilayah Dusun Atas, Kapuas dan Kahayan yaitu Tumenggung Surapati/Tumenggung Yang Pati Jaya Raja (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Pangeran+Antasari";
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
													}else if($hasil['candidates'][0]['subject_id'] == "cutnyakdien" || $hasil['candidates'][0]['subject_id'] == "cutnyakdien1"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/2/2b/Tjoet_Nya%27_Dhien.jpg/220px-Tjoet_Nya%27_Dhien.jpg";
														$data['body']['contents'][0]['text'] = "Cut Nyak Dhien";
														$data['body']['contents'][1]['text'] = "Lahir di Lampadang, Kerajaan Aceh, 1848 – Meninggal di Sumedang, Jawa Barat, 6 November 1908 (dimakamkan di Gunung Puyuh, Sumedang) \n\nAdalah seorang Pahlawan Nasional Indonesia dari Aceh yang berjuang melawan Belanda pada masa Perang Aceh. \nSetelah wilayah VI Mukim diserang, ia mengungsi, sementara suaminya Ibrahim Lamnga bertempur melawan Belanda. Ibrahim Lamnga tewas di Gle Tarum pada tanggal 29 Juni 1878 yang menyebabkan Cut Nyak Dhien sangat marah dan bersumpah hendak menghancurkan Belanda. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Cut+Nyak+Dhien";
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
													}else if($hasil['candidates'][0]['subject_id'] == "imambonjol" ){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/3/3e/Portret_van_Tuanku_Imam_Bonjol.jpg/220px-Portret_van_Tuanku_Imam_Bonjol.jpg";
														$data['body']['contents'][0]['text'] = "Tuanku Imam Bonjol";
														$data['body']['contents'][1]['text'] = "Memiliki nama lahir Muhammad Shahab \nLahir di Bonjol, Pasaman, Sumatera Barat, Indonesia 1772 - Meninggal dalam pengasingan dan dimakamkan di Lotak, Pineleng, Minahasa, 6 November 1864 \n\nAdalah salah seorang ulama, pemimpin dan pejuang yang berperang melawan Belanda dalam peperangan yang dikenal dengan nama Perang Padri pada tahun 1803-1838. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Tuanku+Imam+Bonjol";
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
													}else if($hasil['candidates'][0]['subject_id'] == "teukuumar" || $hasil['candidates'][0]['subject_id'] == "teukuumar1"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/Teuku_Umar.jpg/200px-Teuku_Umar.jpg";
														$data['body']['contents'][0]['text'] = "Teuku Umar";
														$data['body']['contents'][1]['text'] = "Lahir di Meulaboh, 1854 - Meninggal di Meulaboh, 11 Februari 1899 \n\nAdalah pahlawan kemerdekaan Indonesia yang berjuang dengan cara berpura-pura bekerjasama dengan Belanda. \nIa melawan Belanda ketika telah mengumpulkan senjata dan uang yang cukup banyak. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Teuku+Umar";
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
													}else if($hasil['candidates'][0]['subject_id'] == "franskaisiepo"){
														$data['hero']['url'] = "http://cdn2.tstatic.net/jateng/foto/bank/images/frans-kaisiepo_20161225_194715.jpg";
														$data['body']['contents'][0]['text'] = "Frans Kaisiepo";
														$data['body']['contents'][1]['text'] = "Lahir di Wardo, Biak, Papua, 10 Oktober 1921 – Meninggal di Jayapura, Papua, 10 April 1979 pada umur 57 tahun \n\nAdalah pahlawan nasional Indonesia dari Papua. Frans terlibat dalam Konferensi Malino tahun 1946 yang membicarakan mengenai pembentukan Republik Indonesia Serikat sebagai wakil dari Papua. Ia mengusulkan nama Irian, kata dalam bahasa Biak yang berarti tempat yang panas. \nSelain itu, ia juga pernah menjabat sebagai Gubernur Papua antara tahun 1964-1973. Ia dimakamkan di Taman Makam Pahlawan Cendrawasih, Jayapura. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Frans+Kaisiepo";
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
													}else if($hasil['candidates'][0]['subject_id'] == "juandakartawijaya" || $hasil['candidates'][0]['subject_id'] == "juandakartawijaya1" || $hasil['candidates'][0]['subject_id'] == "juandakartawijaya2"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/8/84/Djuanda_Kartawidjaja.jpg/220px-Djuanda_Kartawidjaja.jpg";
														$data['body']['contents'][0]['text'] = "Ir. Haji Raden Djoeanda Kartawidjaja";
														$data['body']['contents'][1]['text'] = "Lahir di Tasikmalaya, Hindia Belanda, 14 Januari 1911 – Meninggal di Jakarta, 7 November 1963 pada umur 52 tahun \n\nAdalah Perdana Menteri Indonesia ke-10 sekaligus yang terakhir. Ia menjabat dari 9 April 1957 hingga 9 Juli 1959. Setelah itu ia menjabat sebagai Menteri Keuangan dalam Kabinet Kerja I. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Raden+Djoeanda+Kartawidjaja";
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
													}else if($hasil['candidates'][0]['subject_id'] == "patimura" || $hasil['candidates'][0]['subject_id'] == "patimura1" || $hasil['candidates'][0]['subject_id'] == "patimura2"){
														$data['hero']['url'] = "https://cdns.klimg.com/merdeka.com/i/w/tokoh/2012/03/15/4595/200x300/kapitan-pattimura.jpg";
														$data['body']['contents'][0]['text'] = "Kapitan Pattimura (atau Thomas Matulessy)";
														$data['body']['contents'][1]['text'] = "Lahir di Haria, pulau Saparua, Maluku, 8 Juni 1783 – Meninggal di Ambon, Maluku, 16 Desember 1817 pada umur 34 tahun \n\nDikenal dengan nama Kapitan Pattimura adalah pahlawan Maluku dan merupakan Pahlawan nasional Indonesia. \nMenurut buku biografi Pattimura versi pemerintah yang pertama kali terbit, M Sapija menulis bahwa pahlawan Pattimura tergolong turunan bangsawan dan berasal dari Nusa Ina (Seram). Ayahnya yang bernama Antoni Mattulessy adalah anak dari Kasimiliali Pattimura Mattulessy. Yang terakhir ini adalah putra raja Sahulau. Sahulau merupakan nama orang di negeri yang terletak dalam sebuah teluk di Seram. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Kapitan+Pattimura";
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
													}else if($hasil['candidates'][0]['subject_id'] == "sultanmahmud" || $hasil['candidates'][0]['subject_id'] == "sultanmahmud1" ){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/2/2e/Mahmud_Badaruddin_II.jpg/220px-Mahmud_Badaruddin_II.jpg";
														$data['body']['contents'][0]['text'] = "Sultan Mahmud Badaruddin II";
														$data['body']['contents'][1]['text'] = "Lahir di Palembang, 1767 Meningggal di Ternate, 26 September 1852) \n\nAdalah pemimpin kesultanan Palembang-Darussalam selama dua periode (1803-1813, 1818-1821), setelah masa pemerintahan ayahnya, Sultan Muhammad Bahauddin (1776-1803). Nama aslinya sebelum menjadi Sultan adalah Raden Hasan Pangeran Ratu. \nDalam masa pemerintahannya, ia beberapa kali memimpin pertempuran melawan Inggris dan Belanda, di antaranya yang disebut Perang Menteng. Pada tangga 14 Juli 1821, ketika Belanda berhasil menguasai Palembang, Sultan Mahmud Badaruddin II dan keluarga ditangkap dan diasingkan ke Ternate. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Sultan+Mahmud+Badaruddin+II";
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
													}else if($hasil['candidates'][0]['subject_id'] == "otto" || $hasil['candidates'][0]['subject_id'] == "otto1" || $hasil['candidates'][0]['subject_id'] == "otto2"){
														$message  = new TextMessageBuilder(" \n");
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/Oto_Iskandar_di_Nata_Youth.jpg/280px-Oto_Iskandar_di_Nata_Youth.jpg";
														$data['body']['contents'][0]['text'] = "Raden Otto Iskandar Dinata";
														$data['body']['contents'][1]['text'] = "Lahir di Bandung, Jawa Barat, 31 Maret 1897 – Meninggal di Mauk, Tangerang, Banten, 20 Desember 1945 pada umur 48 tahun \n\nAdalah salah satu Pahlawan Nasional Indonesia. Ia mendapat nama julukan si Jalak Harupat. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Raden+Otto+Iskandardinata";
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
													}else if($hasil['candidates'][0]['subject_id'] == "husnithamrin" || $hasil['candidates'][0]['subject_id'] == "husnithamrin1" || $hasil['candidates'][0]['subject_id'] == "husnithamrin2"){
														$data['hero']['url'] = "https://cdns.klimg.com/merdeka.com/i/w/tokoh/2012/03/15/4548/200x300/mohammad-hoesni-thamrin.jpg";
														$data['body']['contents'][0]['text'] = "Mohammad Husni Thamrin";
														$data['body']['contents'][1]['text'] = "Lahir di Weltevreden, Batavia, 16 Februari 1894 – Meninggal di Senen, Batavia, 11 Januari 1941 pada umur 46 tahun \n\nAdalah seorang politisi era Hindia Belanda yang kemudian dianugerahi gelar pahlawan nasional Indonesia. \nMunculnya  Muhammad Husni  Thamrin sebagai  tokoh  pergerakan  yang  berkaliber  nasional  tidaklah  tidak  mudah.  Untuk mencapai  tingkat  itu  ia  memulai  dari  bawah,  dari  tingkat lokal. Dia memulai geraknya  sebagai  seorang  tokoh  (lokal)  Betawi.  (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Mohammad+Husni+Thamrin";
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
													}else if($hasil['candidates'][0]['subject_id'] == "cutmeutia" || $hasil['candidates'][0]['subject_id'] == "cutmeutia1"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/id/thumb/5/5a/Cut_Nyak_Meutia.jpg/220px-Cut_Nyak_Meutia.jpg";
														$data['body']['contents'][0]['text'] = "Tjoet Nyak Meutia";
														$data['body']['contents'][1]['text'] = "Lahir di Keureutoe, Pirak, Aceh Utara, 1870 – Meninggal di Alue Kurieng, Aceh, 24 Oktober 1910 \n\nAdalah pahlawan nasional Indonesia dari daerah Aceh. Ia dimakamkan di Alue Kurieng, Aceh. \nAwalnya Tjoet Meutia melakukan perlawanan terhadap Belanda bersama suaminya Teuku Muhammad atau Teuku Tjik Tunong. Namun pada bulan Maret 1905, Tjik Tunong berhasil ditangkap Belanda dan dihukum mati di tepi pantai Lhokseumawe. Sebelum meninggal, Teuku Tjik Tunong berpesan kepada sahabatnya Pang Nagroe agar mau menikahi istrinya dan merawat anaknya Teuku Raja Sabi. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Cut+Nyak+Meutia";
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
													}else if($hasil['candidates'][0]['subject_id'] == "cokroaminoto"){
														$message  = new TextMessageBuilder(" \n");
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/e/e2/HOS_Tjokroaminoto%2C_20_Mei_Pelopor_17_Agustus%2C_p43.jpg/220px-HOS_Tjokroaminoto%2C_20_Mei_Pelopor_17_Agustus%2C_p43.jpg";
														$data['body']['contents'][0]['text'] = "Raden Hadji Oemar Said Tjokroaminoto";
														$data['body']['contents'][1]['text'] = "Lahir di Ponorogo, Jawa Timur, 16 Agustus 1882 – Meninggal di Yogyakarta, Indonesia, 17 Desember 1934 pada umur 52 tahun \n\nBeliau lebih dikenal dengan nama H.O.S Cokroaminoto, merupakan salah satu pemimpin organisasi pertama di Indonesia, yaitu Sarekat Islam (SI). (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Raden+Hadji+Oemar+Said+Tjokroaminoto";
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
														
													}else if($hasil['candidates'][0]['subject_id'] == "diponegoro" || $hasil['candidates'][0]['subject_id'] == "diponegoro1"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/2/2c/Diponegoro.jpg/220px-Diponegoro.jpg";
														$data['body']['contents'][0]['text'] = "Bendara Pangeran Harya Dipanegara";
														$data['body']['contents'][1]['text'] = "Atau yang lebih dikenal dengan nama Pangeran Diponegoro \nLahir di Ngayogyakarta Hadiningrat, 11 November 1785 – Meninggal di Makassar, Hindia Belanda, 8 Januari 1855 pada umur 69 tahun \n\nPangeran Diponegoro adalah putra sulung dari Sultan Hamengkubuwana III, raja ketiga di Kesultanan Yogyakarta. Pangeran Diponegoro terkenal karena memimpin Perang Diponegoro/Perang Jawa (1825-1830) melawan pemerintah Hindia Belanda. Perang tersebut tercatat sebagai perang dengan korban paling besar dalam sejarah Indonesia. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Pangeran+Diponegoro";
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
													}else if($hasil['candidates'][0]['subject_id'] == "fatmawati"){
														$data['hero']['url'] = "https://cdns.klimg.com/merdeka.com/i/w/tokoh/2012/03/15/4638/200x300/fatmawati-soekarno.jpg";
														$data['body']['contents'][0]['text'] = "Fatmawati";
														$data['body']['contents'][1]['text'] = "Lahir di Bengkulu, 5 Februari 1923 – Meninggal di Kuala Lumpur, Malaysia, 14 Mei 1980 pada umur 57 tahun \n\nMemiliki nama asli Fatimah yang adalah istri dari Presiden Indonesia pertama Soekarno. Ia menjadi Ibu Negara Indonesia pertama dari tahun 1945 hingga tahun 1967 dan merupakan istri ke-3 dari Presiden Pertama Indonesia, Soekarno. Ia juga dikenal akan jasanya dalam menjahit Bendera Pusaka Sang Saka Merah Putih yang turut dikibarkan pada upacara Proklamasi Kemerdekaan Indonesia di Jakarta pada tanggal 17 Agustus 1945. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Fatmawati";
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
														
													}
													return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													 
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
		\n\nDibuat oleh : Miky Setiawan (2018) \nTerimakasih telah menggunakan bot ini! \n\nJika bot ini tidak dapat bekerja dengan baik, mohon email : mikysetiawan@gmail.com");
														$result = $bot->replyMessage($event['replyToken'], $message);
														return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													 }else{
														$flexTemplate = file_get_contents("open_camera.json"); // template flex message
														//Editing template
														$data = json_decode($flexTemplate, true);
														$result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
															'replyToken' => $event['replyToken'],
															'messages'   => [
																[
																	'type'     => 'flex',
																	'altText'  => 'Info Pahlawan',
																	'contents' => json_decode($flexTemplate)
																]
															],
														]);
														return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
													 }
									 		}
									}} else {
										//message from single user
										if($event['message']['type'] == 'image')
										{
											$kairos = new Kairos();
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
													$flexTemplate = file_get_contents("flex_message.json"); // template flex message
													//Editing template
													$data = json_decode($flexTemplate, true);

													if($hasil['candidates'][0]['subject_id'] == "soekarno" || $hasil['candidates'][0]['subject_id'] == "soekarno1"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/0/01/Presiden_Sukarno.jpg/220px-Presiden_Sukarno.jpg";
														$data['body']['contents'][0]['text'] = "Dr.(H.C.) Ir. H. Soekarno";
														$data['body']['contents'][1]['text'] = "Memiliki nama lahir : Koesno Sosrodihardjo \nLahir di Surabaya, Jawa Timur, 6 Juni 1901 – Meninggal di Jakarta, 21 Juni 1970 pada umur 69 tahun \nMerupakan presiden Indonesia yang pertama menjabat pada periode 1945–1966. \n\nIa memainkan peranan penting dalam memerdekakan bangsa Indonesia dari penjajahan Belanda. \nIa adalah Proklamator Kemerdekaan Indonesia (bersama dengan Mohammad Hatta) yang terjadi pada tanggal 17 Agustus 1945. \n\nSoekarno adalah yang pertama kali mencetuskan konsep mengenai Pancasila sebagai dasar negara Indonesia dan ia sendiri yang menamainya.(WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Ir+Soekarno";
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
													}else if($hasil['candidates'][0]['subject_id'] == "kartini"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/2/23/COLLECTIE_TROPENMUSEUM_Portret_van_Raden_Ajeng_Kartini_TMnr_10018776.jpg/200px-COLLECTIE_TROPENMUSEUM_Portret_van_Raden_Ajeng_Kartini_TMnr_10018776.jpg";
														$data['body']['contents'][0]['text'] = "Raden Adjeng Kartini";
														$data['body']['contents'][1]['text'] = "Lahir di Jepara, Hindia Belanda, 21 April 1879 - Meninggal di Rembang, Hindia Belanda, 17 September 1904 pada umur 25 tahun \n\nAdalah seorang tokoh Jawa dan Pahlawan Nasional Indonesia. \nKartini dikenal sebagai pelopor kebangkitan perempuan pribumi. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Raden+Adjeng+Kartini";
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
													}else if($hasil['candidates'][0]['subject_id'] == "soedirman"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Sudirman.jpg/220px-Sudirman.jpg";
														$data['body']['contents'][0]['text'] = "Jenderal Besar Raden Soedirman";
														$data['body']['contents'][1]['text'] = "Lahir 24 Januari 1916 – Meninggal 29 Januari 1950 pada umur 34 tahun \n\nAdalah seorang perwira tinggi Indonesia pada masa Revolusi Nasional Indonesia. \nBeliau menjadi panglima besar Tentara Nasional Indonesia pertama, ia secara luas terus dihormati di Indonesia. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Jendral+Soedirman";
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
													}else if($hasil['candidates'][0]['subject_id'] == "hatta" || $hasil['candidates'][0]['subject_id'] == "hatta1" || $hasil['candidates'][0]['subject_id'] == "hatta2"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/f/ff/Mohammad_Hatta_1950.jpg/220px-Mohammad_Hatta_1950.jpg";
														$data['body']['contents'][0]['text'] = "Dr.(HC) Drs. H. Mohammad Hatta";
														$data['body']['contents'][1]['text'] = "Lahir di Fort de Kock (sekarang Bukittinggi, Sumatera Barat), Hindia Belanda, 12 Agustus 1902 – Meninggal di Jakarta, 14 Maret 1980 pada umur 77 tahun. \n\nAdalah tokoh pejuang, negarawan, ekonom, dan juga Wakil Presiden Indonesia yang pertama. \nIa bersama Soekarno memainkan peranan penting untuk memerdekakan bangsa Indonesia dari penjajahan Belanda sekaligus memproklamirkannya pada 17 Agustus 1945. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Mohammad+Hatta";
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
													}else if($hasil['candidates'][0]['subject_id'] == "tomo" || $hasil['candidates'][0]['subject_id'] == "tomo1"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/e/ed/Bung_Tomo.jpg/220px-Bung_Tomo.jpg";
														$data['body']['contents'][0]['text'] = "Sutomo";
														$data['body']['contents'][1]['text'] = "Lahir di Surabaya, Jawa Timur, 3 Oktober 1920 – Meninggal di Padang Arafah, Arab Saudi, 7 Oktober 1981 pada umur 61 tahun \n\nLebih dikenal dengan sapaan akrab oleh rakyat sebagai Bung Tomo, adalah pahlawan yang terkenal karena peranannya dalam membangkitkan semangat rakyat untuk melawan kembalinya penjajah Belanda melalui tentara NICA, yang berakhir dengan pertempuran 10 November 1945 yang hingga kini diperingati sebagai Hari Pahlawan. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Sutomo";
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
													}else if($hasil['candidates'][0]['subject_id'] == "ngurahrai" || $hasil['candidates'][0]['subject_id'] == "ngurahrai1"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/id/thumb/f/ff/Gusti_ngurah_rai.jpg/220px-Gusti_ngurah_rai.jpg";
														$data['body']['contents'][0]['text'] = "Kolonel TNI Anumerta I Gusti Ngurah Rai";
														$data['body']['contents'][1]['text'] = "Lahir di Desa Carangsari, Petang, Kabupaten Badung, Bali, Hindia Belanda, 30 Januari 1917 – Meninggal di Marga, Tabanan, Bali, Indonesia, 20 November 1946 pada umur 29 tahun \n\nAdalah seorang pahlawan Indonesia dari Kabupaten Badung, Bali. \nNgurah Rai memiliki pasukan yang bernama pasukan 'Ciung Wanara' yang melakukan pertempuran terakhir yang dikenal dengan nama Puputan Margarana. (Puputan, dalam bahasa bali, berarti habis-habisan, sedangkan Margarana berarti Pertempuran di Marga. \nMarga adalah sebuah desa ibukota kecamatan di pelosok Kabupaten Tabanan, Bali) Di tempat puputan tersebut lalu didirikan Taman Makam Pahlawan Margarana. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=I+Gusti+Ngurah+Rai";
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
													}else if($hasil['candidates'][0]['subject_id'] == "antasari" || $hasil['candidates'][0]['subject_id'] == "antasari1"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/id/thumb/c/c8/Pangeran_Antasari_Museum_Lambung_Mangkurat.JPG/220px-Pangeran_Antasari_Museum_Lambung_Mangkurat.JPG";
														$data['body']['contents'][0]['text'] = "Pangeran Antasari";
														$data['body']['contents'][1]['text'] = "Lahir di Kayu Tangi, Kesultanan Banjar, 1797 atau 1809 – Meninggal di Bayan Begok, Hindia Belanda, 11 Oktober 1862 pada umur 53 tahun \n\nAdalah seorang Pahlawan Nasional Indonesia.\nIa adalah Sultan Banjar.\nPada 14 Maret 1862, dia dinobatkan sebagai pimpinan pemerintahan tertinggi di Kesultanan Banjar (Sultan Banjar) dengan menyandang gelar Panembahan Amiruddin Khalifatul Mukminin dihadapan para kepala suku Dayak dan adipati (gubernur) penguasa wilayah Dusun Atas, Kapuas dan Kahayan yaitu Tumenggung Surapati/Tumenggung Yang Pati Jaya Raja (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Pangeran+Antasari";
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
													}else if($hasil['candidates'][0]['subject_id'] == "cutnyakdien" || $hasil['candidates'][0]['subject_id'] == "cutnyakdien1"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/2/2b/Tjoet_Nya%27_Dhien.jpg/220px-Tjoet_Nya%27_Dhien.jpg";
														$data['body']['contents'][0]['text'] = "Cut Nyak Dhien";
														$data['body']['contents'][1]['text'] = "Lahir di Lampadang, Kerajaan Aceh, 1848 – Meninggal di Sumedang, Jawa Barat, 6 November 1908 (dimakamkan di Gunung Puyuh, Sumedang) \n\nAdalah seorang Pahlawan Nasional Indonesia dari Aceh yang berjuang melawan Belanda pada masa Perang Aceh. \nSetelah wilayah VI Mukim diserang, ia mengungsi, sementara suaminya Ibrahim Lamnga bertempur melawan Belanda. Ibrahim Lamnga tewas di Gle Tarum pada tanggal 29 Juni 1878 yang menyebabkan Cut Nyak Dhien sangat marah dan bersumpah hendak menghancurkan Belanda. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Cut+Nyak+Dhien";
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
													}else if($hasil['candidates'][0]['subject_id'] == "imambonjol" ){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/3/3e/Portret_van_Tuanku_Imam_Bonjol.jpg/220px-Portret_van_Tuanku_Imam_Bonjol.jpg";
														$data['body']['contents'][0]['text'] = "Tuanku Imam Bonjol";
														$data['body']['contents'][1]['text'] = "Memiliki nama lahir Muhammad Shahab \nLahir di Bonjol, Pasaman, Sumatera Barat, Indonesia 1772 - Meninggal dalam pengasingan dan dimakamkan di Lotak, Pineleng, Minahasa, 6 November 1864 \n\nAdalah salah seorang ulama, pemimpin dan pejuang yang berperang melawan Belanda dalam peperangan yang dikenal dengan nama Perang Padri pada tahun 1803-1838. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Tuanku+Imam+Bonjol";
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
													}else if($hasil['candidates'][0]['subject_id'] == "teukuumar" || $hasil['candidates'][0]['subject_id'] == "teukuumar1"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/Teuku_Umar.jpg/200px-Teuku_Umar.jpg";
														$data['body']['contents'][0]['text'] = "Teuku Umar";
														$data['body']['contents'][1]['text'] = "Lahir di Meulaboh, 1854 - Meninggal di Meulaboh, 11 Februari 1899 \n\nAdalah pahlawan kemerdekaan Indonesia yang berjuang dengan cara berpura-pura bekerjasama dengan Belanda. \nIa melawan Belanda ketika telah mengumpulkan senjata dan uang yang cukup banyak. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Teuku+Umar";
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
													}else if($hasil['candidates'][0]['subject_id'] == "franskaisiepo"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/Teuku_Umar.jpg/200px-Teuku_Umar.jpg";
														$data['body']['contents'][0]['text'] = "Frans Kaisiepo";
														$data['body']['contents'][1]['text'] = "Lahir di Wardo, Biak, Papua, 10 Oktober 1921 – Meninggal di Jayapura, Papua, 10 April 1979 pada umur 57 tahun \n\nAdalah pahlawan nasional Indonesia dari Papua. Frans terlibat dalam Konferensi Malino tahun 1946 yang membicarakan mengenai pembentukan Republik Indonesia Serikat sebagai wakil dari Papua. Ia mengusulkan nama Irian, kata dalam bahasa Biak yang berarti tempat yang panas. \nSelain itu, ia juga pernah menjabat sebagai Gubernur Papua antara tahun 1964-1973. Ia dimakamkan di Taman Makam Pahlawan Cendrawasih, Jayapura. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Frans+Kaisiepo";
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
													}else if($hasil['candidates'][0]['subject_id'] == "juandakartawijaya" || $hasil['candidates'][0]['subject_id'] == "juandakartawijaya1" || $hasil['candidates'][0]['subject_id'] == "juandakartawijaya2"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/8/84/Djuanda_Kartawidjaja.jpg/220px-Djuanda_Kartawidjaja.jpg";
														$data['body']['contents'][0]['text'] = "Ir. Haji Raden Djoeanda Kartawidjaja";
														$data['body']['contents'][1]['text'] = "Lahir di Tasikmalaya, Hindia Belanda, 14 Januari 1911 – Meninggal di Jakarta, 7 November 1963 pada umur 52 tahun \n\nAdalah Perdana Menteri Indonesia ke-10 sekaligus yang terakhir. Ia menjabat dari 9 April 1957 hingga 9 Juli 1959. Setelah itu ia menjabat sebagai Menteri Keuangan dalam Kabinet Kerja I. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Raden+Djoeanda+Kartawidjaja";
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
													}else if($hasil['candidates'][0]['subject_id'] == "patimura" || $hasil['candidates'][0]['subject_id'] == "patimura1" || $hasil['candidates'][0]['subject_id'] == "patimura2"){
														$data['hero']['url'] = "https://cdns.klimg.com/merdeka.com/i/w/tokoh/2012/03/15/4595/200x300/kapitan-pattimura.jpg";
														$data['body']['contents'][0]['text'] = "Kapitan Pattimura (atau Thomas Matulessy)";
														$data['body']['contents'][1]['text'] = "Lahir di Haria, pulau Saparua, Maluku, 8 Juni 1783 – Meninggal di Ambon, Maluku, 16 Desember 1817 pada umur 34 tahun \n\nDikenal dengan nama Kapitan Pattimura adalah pahlawan Maluku dan merupakan Pahlawan nasional Indonesia. \nMenurut buku biografi Pattimura versi pemerintah yang pertama kali terbit, M Sapija menulis bahwa pahlawan Pattimura tergolong turunan bangsawan dan berasal dari Nusa Ina (Seram). Ayahnya yang bernama Antoni Mattulessy adalah anak dari Kasimiliali Pattimura Mattulessy. Yang terakhir ini adalah putra raja Sahulau. Sahulau merupakan nama orang di negeri yang terletak dalam sebuah teluk di Seram. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Kapitan+Pattimura";
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
													}else if($hasil['candidates'][0]['subject_id'] == "sultanmahmud" || $hasil['candidates'][0]['subject_id'] == "sultanmahmud1" ){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/2/2e/Mahmud_Badaruddin_II.jpg/220px-Mahmud_Badaruddin_II.jpg";
														$data['body']['contents'][0]['text'] = "Sultan Mahmud Badaruddin II";
														$data['body']['contents'][1]['text'] = "Lahir di Palembang, 1767 Meningggal di Ternate, 26 September 1852) \n\nAdalah pemimpin kesultanan Palembang-Darussalam selama dua periode (1803-1813, 1818-1821), setelah masa pemerintahan ayahnya, Sultan Muhammad Bahauddin (1776-1803). Nama aslinya sebelum menjadi Sultan adalah Raden Hasan Pangeran Ratu. \nDalam masa pemerintahannya, ia beberapa kali memimpin pertempuran melawan Inggris dan Belanda, di antaranya yang disebut Perang Menteng. Pada tangga 14 Juli 1821, ketika Belanda berhasil menguasai Palembang, Sultan Mahmud Badaruddin II dan keluarga ditangkap dan diasingkan ke Ternate. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Sultan+Mahmud+Badaruddin+II";
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
													}else if($hasil['candidates'][0]['subject_id'] == "otto" || $hasil['candidates'][0]['subject_id'] == "otto1" || $hasil['candidates'][0]['subject_id'] == "otto2"){
														$message  = new TextMessageBuilder(" \n");
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/Oto_Iskandar_di_Nata_Youth.jpg/280px-Oto_Iskandar_di_Nata_Youth.jpg";
														$data['body']['contents'][0]['text'] = "Raden Otto Iskandar Dinata";
														$data['body']['contents'][1]['text'] = "Lahir di Bandung, Jawa Barat, 31 Maret 1897 – Meninggal di Mauk, Tangerang, Banten, 20 Desember 1945 pada umur 48 tahun \n\nAdalah salah satu Pahlawan Nasional Indonesia. Ia mendapat nama julukan si Jalak Harupat. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Raden+Otto+Iskandardinata";
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
													}else if($hasil['candidates'][0]['subject_id'] == "husnithamrin" || $hasil['candidates'][0]['subject_id'] == "husnithamrin1" || $hasil['candidates'][0]['subject_id'] == "husnithamrin2"){
														$data['hero']['url'] = "https://cdns.klimg.com/merdeka.com/i/w/tokoh/2012/03/15/4548/200x300/mohammad-hoesni-thamrin.jpg";
														$data['body']['contents'][0]['text'] = "Mohammad Husni Thamrin";
														$data['body']['contents'][1]['text'] = "Lahir di Weltevreden, Batavia, 16 Februari 1894 – Meninggal di Senen, Batavia, 11 Januari 1941 pada umur 46 tahun \n\nAdalah seorang politisi era Hindia Belanda yang kemudian dianugerahi gelar pahlawan nasional Indonesia. \nMunculnya  Muhammad Husni  Thamrin sebagai  tokoh  pergerakan  yang  berkaliber  nasional  tidaklah  tidak  mudah.  Untuk mencapai  tingkat  itu  ia  memulai  dari  bawah,  dari  tingkat lokal. Dia memulai geraknya  sebagai  seorang  tokoh  (lokal)  Betawi.  (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Mohammad+Husni+Thamrin";
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
													}else if($hasil['candidates'][0]['subject_id'] == "cutmeutia" || $hasil['candidates'][0]['subject_id'] == "cutmeutia1"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/id/thumb/5/5a/Cut_Nyak_Meutia.jpg/220px-Cut_Nyak_Meutia.jpg";
														$data['body']['contents'][0]['text'] = "Tjoet Nyak Meutia";
														$data['body']['contents'][1]['text'] = "Lahir di Keureutoe, Pirak, Aceh Utara, 1870 – Meninggal di Alue Kurieng, Aceh, 24 Oktober 1910 \n\nAdalah pahlawan nasional Indonesia dari daerah Aceh. Ia dimakamkan di Alue Kurieng, Aceh. \nAwalnya Tjoet Meutia melakukan perlawanan terhadap Belanda bersama suaminya Teuku Muhammad atau Teuku Tjik Tunong. Namun pada bulan Maret 1905, Tjik Tunong berhasil ditangkap Belanda dan dihukum mati di tepi pantai Lhokseumawe. Sebelum meninggal, Teuku Tjik Tunong berpesan kepada sahabatnya Pang Nagroe agar mau menikahi istrinya dan merawat anaknya Teuku Raja Sabi. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Cut+Nyak+Meutia";
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
													}else if($hasil['candidates'][0]['subject_id'] == "cokroaminoto"){
														$message  = new TextMessageBuilder(" \n");
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/e/e2/HOS_Tjokroaminoto%2C_20_Mei_Pelopor_17_Agustus%2C_p43.jpg/220px-HOS_Tjokroaminoto%2C_20_Mei_Pelopor_17_Agustus%2C_p43.jpg";
														$data['body']['contents'][0]['text'] = "Raden Hadji Oemar Said Tjokroaminoto";
														$data['body']['contents'][1]['text'] = "Lahir di Ponorogo, Jawa Timur, 16 Agustus 1882 – Meninggal di Yogyakarta, Indonesia, 17 Desember 1934 pada umur 52 tahun \n\nBeliau lebih dikenal dengan nama H.O.S Cokroaminoto, merupakan salah satu pemimpin organisasi pertama di Indonesia, yaitu Sarekat Islam (SI). (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Raden+Hadji+Oemar+Said+Tjokroaminoto";
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
														
													}else if($hasil['candidates'][0]['subject_id'] == "diponegoro" || $hasil['candidates'][0]['subject_id'] == "diponegoro1"){
														$data['hero']['url'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/2/2c/Diponegoro.jpg/220px-Diponegoro.jpg";
														$data['body']['contents'][0]['text'] = "Bendara Pangeran Harya Dipanegara";
														$data['body']['contents'][1]['text'] = "Atau yang lebih dikenal dengan nama Pangeran Diponegoro \nLahir di Ngayogyakarta Hadiningrat, 11 November 1785 – Meninggal di Makassar, Hindia Belanda, 8 Januari 1855 pada umur 69 tahun \n\nPangeran Diponegoro adalah putra sulung dari Sultan Hamengkubuwana III, raja ketiga di Kesultanan Yogyakarta. Pangeran Diponegoro terkenal karena memimpin Perang Diponegoro/Perang Jawa (1825-1830) melawan pemerintah Hindia Belanda. Perang tersebut tercatat sebagai perang dengan korban paling besar dalam sejarah Indonesia. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Pangeran+Diponegoro";
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
													}else if($hasil['candidates'][0]['subject_id'] == "fatmawati"){
														$data['hero']['url'] = "https://cdns.klimg.com/merdeka.com/i/w/tokoh/2012/03/15/4638/200x300/fatmawati-soekarno.jpg";
														$data['body']['contents'][0]['text'] = "Fatmawati";
														$data['body']['contents'][1]['text'] = "Lahir di Bengkulu, 5 Februari 1923 – Meninggal di Kuala Lumpur, Malaysia, 14 Mei 1980 pada umur 57 tahun \n\nMemiliki nama asli Fatimah yang adalah istri dari Presiden Indonesia pertama Soekarno. Ia menjadi Ibu Negara Indonesia pertama dari tahun 1945 hingga tahun 1967 dan merupakan istri ke-3 dari Presiden Pertama Indonesia, Soekarno. Ia juga dikenal akan jasanya dalam menjahit Bendera Pusaka Sang Saka Merah Putih yang turut dikibarkan pada upacara Proklamasi Kemerdekaan Indonesia di Jakarta pada tanggal 17 Agustus 1945. (WIKIPEDIA)";
														$data['footer']['contents'][0]['action']['uri'] = "https://www.google.com/search?q=Fatmawati";
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
														
													}
													return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
								
												}else{
													$message  = new TextMessageBuilder('Maaf, Karena terbatasnya waktu dan biaya, kemungkinan foto pahlawan yang anda kirim belum kami masukkan. Bot tidak dapat mengenali foto ini, silahkan coba lagi atau ketik "help" untuk melihat nama pahlawan yang sudah ada di server');
													$result = $bot->replyMessage($event['replyToken'], $message);
													return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
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
\n\nDibuat oleh : Miky Setiawan (2018) \nTerimakasih telah menggunakan bot ini! \n\nJika bot ini tidak dapat bekerja dengan baik, mohon email : mikysetiawan@gmail.com");
											$result = $bot->replyMessage($event['replyToken'], $message);
											return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
										 }else{
											$flexTemplate = file_get_contents("open_camera.json"); // template flex message
											//Editing template
											$data = json_decode($flexTemplate, true);
											$result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
												'replyToken' => $event['replyToken'],
												'messages'   => [
													[
														'type'     => 'flex',
														'altText'  => 'Info Pahlawan',
														'contents' => json_decode($flexTemplate)
													]
												],
											]);
											return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
										 }
										}
									 }
						}
				}
		}

});

$app->run();
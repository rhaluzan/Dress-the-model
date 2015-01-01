<?php
namespace notigo\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;

define("setDate", "now");

class GameController
{

    public function indexAction(Request $request, Application $app)
    {



    }

    public function addPlayerAction(Request $request, Application $app)
    {

        $user_profile   = $app['facebook']->api('/'.$app['facebook']->getUser());

        $app['db']->insert("users", array(
                    "fbid"          => $user_profile['id'],
                    "name"          => $user_profile['name'],
                    "link"          => $user_profile['link'],
                    "username"      => $user_profile['username'],
                    "gender"        => $user_profile['gender'],
                    "email"         => $user_profile['email'],
                    // "notifications" => $notifications,
                    "verified"      => $user_profile['verified']
                ));

        //echo "added";
        return true;

    }

    public function checkPlayerAction(Request $request, Application $app, $player)
    {

        $user =  $app['db']->fetchAssoc('SELECT count(fbid) as count FROM users WHERE fbid = ?', array($player));

        if($user && $user['count'] > 0)
        {
            return true;
        } else {
            return false;
        }

    }

    public function getPlayerPointsAction(Request $request, Application $app, $player)
    {

        $date = constant("setDate");
        // $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday this week", strtotime($date)));
        // $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        if(date('D', strtotime($date)) == "Sun"):
            $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday last week", strtotime($date)));
            $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        else:
            //echo "nope, not monday.";
            $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday this week", strtotime($date)));
            $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        endif;

        // echo date('Y-m-d H:i:s', strtotime("00:00:00 Monday this week", strtotime($date))) . " - " .  date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));

        $userPoints =  $app['db']->fetchAssoc(' SELECT SUM(`pointsGames`) as allPoints
                                                FROM games
                                                WHERE  `user_fbid` =  ?
                                                AND timestamp BETWEEN ? AND ?', array($player, $timestamp, $timestamp1));

        if($userPoints['allPoints'] == 0) {
            return "0";
        } else {
            return $userPoints['allPoints'];
        }

    }

    public function getPlayerRank(Request $request, Application $app, $player)
    {

        $date = constant("setDate");
        // $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday this week", strtotime($date)));
        // $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        if(date('D', strtotime($date)) == "Sun"):
            $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday last week", strtotime($date)));
            $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        else:
            //echo "nope, not monday.";
            $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday this week", strtotime($date)));
            $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        endif;
        //$user =  $app['db']->fetchAssoc('SELECT COUNT( * ) AS rank FROM games WHERE  `pointsGames` >= (SELECT SUM(  `pointsGames` ) FROM games WHERE  `user_fbid` =  ?)', array($player));

        $user =  $app['db']->fetchAssoc("SELECT * FROM (SELECT s.*, @rank := @rank + 1 rank FROM (SELECT user_fbid, sum(pointsGames) TotalPoints, timestamp FROM games WHERE timestamp BETWEEN ? AND ? GROUP BY user_fbid) s, (SELECT @rank := 0) init ORDER BY TotalPoints DESC) r WHERE user_fbid = ?", array($timestamp, $timestamp1, $player));

        return $user['rank'];

    }

    public function getAllPlayers(Request $request, Application $app)
    {

        $date = constant("setDate");
        // $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday this week", strtotime($date)));
        // $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        if(date('D', strtotime($date)) == "Sun"):
            $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday last week", strtotime($date)));
            $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        else:
            //echo "nope, not monday.";
            $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday this week", strtotime($date)));
            $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        endif;
        //$user =  $app['db']->fetchAssoc('SELECT COUNT( * ) AS rank FROM games WHERE  `pointsGames` >= (SELECT SUM(  `pointsGames` ) FROM games WHERE  `user_fbid` =  ?)', array($player));

        $user =  $app['db']->fetchAssoc("SELECT count(DISTINCT `user_fbid`) AS count FROM games WHERE timestamp BETWEEN ? AND ?", array($timestamp, $timestamp1));

        return $user['count'];

    }


    public function checkIfPlayedAction(Request $request, Application $app, $player)
    {

        $date = constant("setDate");
        $timestamp = date('Y-m-d H:i:s', strtotime("Today 00:00:00", strtotime($date)));
        $timestamp1 = date('Y-m-d H:i:s', strtotime("Tomorrow 00:00:00", strtotime($timestamp)));
        // $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday this week", strtotime($date)));
        // $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        // if(date('D', strtotime($date)) == "Sun"):
        //     $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday last week", strtotime($date)));
        //     $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        // else:
        //     //$"nope, not monday.";
        //     $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday this week", strtotime($date)));
        //     $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        // endif;

        //echo "Now: " . date('d.m.Y H:i:s') . " Midnight tonight: " . date('d.m.Y H:i:s', strtotime('midnight today'));
        // $user =  $app['db']->fetchAssoc('SELECT timestamp
        //                                  FROM games
        //                                  WHERE user_fbid = ? AND type = ? ORDER BY timestamp DESC', array($player, 'game'));
        echo $timestamp . " - " . $timestamp1;
        $count = $app['db']->executeQuery("SELECT * FROM games
                                           WHERE user_fbid = $player
                                           AND type = 'game'
                                           AND timestamp BETWEEN '".$timestamp."' AND '".$timestamp1."'")->rowCount();

        echo $count;
        if($count >= 7)
        {
            return true;
        } else {
            return false;
        }
        // if(strtotime($user['timestamp']) <= strtotime('midnight today', strtotime($date))) {
        //     //echo "not played";
        //     return false;
        // }
        // else {
        //     //echo "did played";
        //     return true;
        // }

    }

    public function fetchPlayedEnvsActions(Request $request, Application $app)
    {

        $fbid       = $app['facebook']->getUser();
        if($fbid)
        {
            $date = constant("setDate");
            $timestamp = date('Y-m-d H:i:s', strtotime("Today 00:00:00", strtotime($date)));
            $timestamp1 = date('Y-m-d H:i:s', strtotime("Tomorrow 00:00:00", strtotime($timestamp)));
            // if(date('D', strtotime($date)) == "Sun"):
            //     $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday last week", strtotime($date)));
            //     $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
            // else:
            //     //$"nope, not monday.";
            //     $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday this week", strtotime($date)));
            //     $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
            // endif;

            #echo date('d.m.Y H:i:s', strtotime($timestamp)) . " - " . date('d.m.Y H:i:s', strtotime($timestamp1));

            $results = $app['db']->fetchAll("SELECT environment
                                             FROM games
                                             WHERE user_fbid = $fbid AND type = 'game' AND timestamp BETWEEN '".$timestamp."' AND '".$timestamp1."'");

            return json_encode($results);
        }

    }

    public function addInvitesAction(Request $request, Application $app)
    {

        $invites = $request->get('invites');
        $invites = json_decode($invites);

        $fbid = $app['facebook']->getUser();

        $date = constant("setDate");
        // $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday this week", strtotime($date)));
        // $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        if(date('D', strtotime($date)) == "Sun"):
            $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday last week", strtotime($date)));
            $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        else:
            //$"nope, not monday.";
            $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday this week", strtotime($date)));
            $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        endif;

        if(is_array($invites)) {
            foreach ($invites as $inv) {

                //$results = $app['db']->fetchAll("");

                $count = $app['db']->executeQuery("SELECT * FROM games
                                                   WHERE user_fbid = $fbid AND fbid = $inv AND type = 'inv' AND timestamp BETWEEN '".$timestamp."' AND '".$timestamp1."'")->rowCount();

                if($count > 0)
                {
                    // do nothing
                } else {
                    $app['db']->insert('games', array('user_fbid' => $app['facebook']->getUser(), 'fbid' => $inv, 'type' => 'inv', 'pointsGames' => 1));
                }
                //SELECT count(*) FROM games WHERE user_fbid = '1172213251' AND fbid = '100002315772018' AND type = 'inv' AND timestamp BETWEEN '2013-11-25 00:00:00' AND '2013-12-02 00:00:00'
            }
            return new Response(json_encode(array("status" => "OK", "msg" => "Does not exist.")), 201);
            // return new Response($invites, 201);
        } else {
            return new Response(json_encode(array("status" => "ERROR", "msg" => "Not inserted to database.")), 201);
        }

    }

    public function addGamePlayAction(Request $request, Application $app)
    {

        $user_profile   = $app['facebook']->api('/'.$app['facebook']->getUser());
        $fbid           = $user_profile['id'];

        if($this->checkIfPlayedAction($request, $app, $fbid))
        {
            return json_encode(array("Error" => "Already played."));
            //return new Response('ERROR: already played.', 201);
        }
        else {
            $environment    = $request->get('env');
            //$pointsGames  = $request->get('pointsGames');
            $pointsGames = 20;

            if($app['db']->insert('games', array('user_fbid' => $fbid, 'environment' => $environment, 'pointsGames' => $pointsGames, 'type' => 'game')))
            {
                return json_encode(array("OK" => "Does not exist."));
                //return new Response('OK', 201);
            }
            else {
                return json_encode(array("Error" => "Not inserted to database."));
                //return new Response('ERROR: not inserted to database.', 201);
            }
        }

    }

    public function fetchEnvironmentAction(Request $request, Application $app, $environment="")
    {

        switch ($environment) {
            case "env-smucanje":
                $otherPics = array(
                        array('text' => 'SALT JOPIČ', 'longtext' => 'Ok... res da piha, ampak saj nisi na morju! :)', 'pic' => 'smucanje-31293_599_main_large.png', 'sequence' => 0),
                        array('text' => 'INFLATABLE Rešilni jopič', 'longtext' => 'Pri padcu na smučeh, te tudi rešilni jopič ne bo rešil! :)', 'pic' => 'smucanje-33846_990_main_large.png', 'sequence' => 0),
                        array('text' => 'ASK ALL SEASON PLAŠČ', 'longtext' => 'Hm... si ti na modni pisti in ne na smučišču mogoče? :)', 'pic' => 'smucanje-51369_990_main_large.png', 'sequence' => 0),
                        array('text' => 'MARSTRAND REVERSIBLE JOPIČ', 'longtext' => 'Tudi če ga okoli obrneš, ne bo primeren za smučanje. :)', 'pic' => 'smucanje-51375_689_main_large.png', 'sequence' => 0),
                         array('text' => 'GRAPHIC JOPA', 'longtext' => 'Zelo dobra izbira oblačila... za v kino! :)', 'pic' => 'smucanje-51392_965_main_large.png', 'sequence' => 0),
                        array('text' => 'DUBLIM Parka JOPIČ', 'longtext' => 'Če oblečeš to jakno za smučanje, boš vsekakor zmagal na tekmi smučarjev »oldtimer-jev«. :)', 'pic' => 'smucanje-55878_729_main_large.png', 'sequence' => 0),
                        array('text' => 'DRIFTLINE MAJICA', 'longtext' => 'A se ti je pokvaril termostat? Zunaj je namreč zima! :)', 'pic' => 'smucanje-50584_519_main_large.png', 'sequence' => 0),
                        array('text' => 'TRAVEL TORBA', 'longtext' => 'Ja, kaj pa imaš notri? Mogoče termofor? :)', 'pic' => 'smucanje-67059_990_main_large.png', 'sequence' => 0)
                    );
                shuffle($otherPics);
                $slices = array_slice($otherPics, 0, 3);
                $clothes = array(
                                array('text' => 'MOUNT PROSTRETCH VELUR', 'longtext' => 'Topel in tanek 100 g velur, ki ga oblečeš nad športnim perilom LIFA - odličen za zimski šport!', 'pic' => 'smucanje-2.png', 'sequence' => 1),
                                array('text' => 'ENIGMA JOPIČ', 'longtext' => 'Tako dober smučarski jopič, da te bo pustil brez besed. <span style="font-weight: bold; color: #d72128;">Oblečeš ga čez velur.</span>', 'pic' => 'smucanje-3.png', 'sequence' => 2),
                                $slices[0],
                                $slices[1],
                                $slices[2]
                            );
                shuffle($clothes);
                return $layers = array( 'tagline'   => 'na smučanju',
                                        'taglineSuccess' => 'na smuko.',
                                        'deg'       => 'five',
                                        'layers'    => $clothes
                                        );
                break;
            case "env-tek_na_smuceh":
                $otherPics = array(
                        array('text' => 'LOGO MAJICA', 'longtext' => 'Lepo, da ponosno nosiš našo znamko, ampak to je odlična izbira za gledanje teka na smučeh po televiziji... :)', 'pic' => 'tek_na_smuceh-50588_001_main_large.png', 'sequence' => 0),
                        array('text' => 'ENIGMA JOPIČ', 'longtext' => 'Za tek na smučeh pa res ne potrebuješ tako drage jakne. :)', 'pic' => 'tek_na_smuceh-62100_535_main_large.png', 'sequence' => 0),
                        array('text' => 'LEGENDARY HLAČE', 'longtext' => 'Stari... Legenda si v teku, ampak te hlače so za tisto drugo smučanje... :)', 'pic' => 'tek_na_smuceh-60359_990_main_large.png', 'sequence' => 0),
                        array('text' => 'MISSION PARKA JOPIČ', 'longtext' => 'Tek na smučeh v tem? To bi bila vsekakor misija nemogoče! :)', 'pic' => 'tek_na_smuceh-62156_980_main_large.png', 'sequence' => 0),
                        array('text' => 'VERGLAS EXPEDITION DOWN PARKA JOPIČ', 'longtext' => 'Če pa v tem plašču tečeš, se vsekakor skuhaš na koncu. :)', 'pic' => 'tek_na_smuceh-66548_204_main_large.png', 'sequence' => 0),
                        array('text' => 'LERWICK DEŽNI JOPIČ', 'longtext' => 'Če bi bil sprehod po dežju, bi bila to odlična izbira. Ampak... tečeš na smučeh. :)', 'pic' => 'tek_na_smuceh-62201_515_main_large.png', 'sequence' => 0),
                        array('text' => 'MARSTRAND LS MAJICA', 'longtext' => 'Če greš v gorsko kočo, je odlična izbira. Ampak pri teku na smučeh bi v njej izgledal res čudno. :)', 'pic' => 'tek_na_smuceh-51486_707_main_large.png', 'sequence' => 0),
                        array('text' => 'HYDRO POWER RIGGIING PLAŠČ', 'longtext' => 'Cesarski pingvin so tako ljubki. Ampak oni res ne tečejo na smučeh. :)', 'pic' => 'tek_na_smuceh-55849_689_main_large.png', 'sequence' => 0)
                    );
                shuffle($otherPics);
                $slices = array_slice($otherPics, 0, 3);
                $clothes = array(
                                array('text' => 'CHALLENGER JOPIČ', 'longtext' => 'Vrhunski tekaški jopič - odlična izbira za tek na smučeh!', 'pic' => 'tek-na-smuceh-2.png', 'sequence' => 1),
                                array('text' => 'NORDIC SKI RACING ROKAVICE', 'longtext' => 'Softshell rokavice za tek na smučeh in pohodništvo. <span style="font-weight: bold; color: #d72128;">Navadno jih nadeneš na koncu.</span>', 'pic' => 'tek-na-smuceh-3.png', 'sequence' => 2),
                                $slices[0],
                                $slices[1],
                                $slices[2]
                            );
                shuffle($clothes);
                return $layers = array( 'tagline'        => 'na teku',
                                        'taglineSuccess' => 'za tek.',
                                        'deg'            => 'five',
                                        'layers'         => $clothes
                                        );
                break;
            case "env-jadranje":
                 $otherPics = array(
                        array('text' => 'DUBLIN DEŽNIK', 'longtext' => 'Odprt ali zaprt - vsekakor ti bo v napoto pri jadranju. :)', 'pic' => 'jadranje-67890_990_main_large.png', 'sequence' => 0),
                        array('text' => 'RIDER SMUČARSKE HLAČE', 'longtext' => 'Am... saj veš da si na jadranju in ne na Krvavcu, kajne? :)', 'pic' => 'jadranje-41058_689_main_large.png', 'sequence' => 0),
                        array('text' => 'SUPREME PUH JOPIČ', 'longtext' => 'Bravo. Odlična izbira, dokler te ne polije prvi val. :)', 'pic' => 'jadranje-62146_570_main_large.png', 'sequence' => 0),
                        array('text' => 'MISSION PARKA JOPIČ', 'longtext' => 'Greš mogoče z jadrnico do Božička? :)', 'pic' => 'jadranje-62156_980_main_large.png', 'sequence' => 0),
                        array('text' => 'GRUMANT FLOW PARKA JOPIČ', 'longtext' => 'Ni zime za Eskime kaj...? Ampak Eskimi ne jadrajo, saj veš to kajne? :)', 'pic' => 'jadranje-51377_222_main_large.png', 'sequence' => 0),
                        array('text' => 'MARSTRAND POLO MAJICA', 'longtext' => 'Delo poklicnega supermodela ni enostavna stvar. Daj no, raje potegni sidro ven. :)', 'pic' => 'jadranje-51486_707_main_large.png', 'sequence' => 0),
                        array('text' => 'SKI TORBA', 'longtext' => 'A v to smučarsko torbo sodijo ribiške palice? :)', 'pic' => 'jadranje-67047_162_main_large.png', 'sequence' => 0),
                        array('text' => 'GRAPHIC JOPA', 'longtext' => '»Šminkiranje« na jadrnici ti gre pa dobro od rok, kaj? :)', 'pic' => 'jadranje-51473_689_main_large.png', 'sequence' => 0)
                    );
                shuffle($otherPics);
                $slices = array_slice($otherPics, 0, 3);
                $clothes = array(
                                array('text' => 'SALT JOPIČ', 'longtext' => 'Za jesensko in spomladansko jadranje si obleči Salt jopič, v katerem izgledaš dobro tudi v mestu!', 'pic' => 'jadranje-2.png', 'sequence' => 1),
                                array('text' => 'JADRALNE ROKAVICE', 'longtext' => 'Vsestranske rokavice, ki jih lahko uporabljaš za jadranje, kolesarjenje in dvigovanje uteži. <span style="font-weight: bold; color: #d72128;">Navadno jih nadeneš na koncu.</span>', 'pic' => 'jadranje-3.png', 'sequence' => 2),
                                $slices[0],
                                $slices[1],
                                $slices[2]
                            );
                shuffle($clothes);
                return $layers = array( 'tagline'   => 'na jadranju',
                                        'taglineSuccess' => 'za jadranje.',
                                        'deg'       => 'twenty',
                                        'layers'    => $clothes
                                        );
                break;
            case "env-klasicni_tek":
                $otherPics = array(
                        array('text' => 'HYDRO POWER kopalke', 'longtext' => 'O! Za takim tekačem bi se pa malo čudno obračali, kaj? :)', 'pic' => 'tek-51329_512_main_large.png', 'sequence' => 0),
                        array('text' => 'GRAPHIC MAJICA', 'longtext' => 'Mislim, da si malo za časom. Nihče ne uporablja bombaža za tek. :)', 'pic' => 'tek-51472_535_main_large.png', 'sequence' => 0),
                        array('text' => 'DUBLIN CATALINA JOPIČ', 'longtext' => 'Zdaj se pa odloči: ali boš tekel ali se sprehajal? :)', 'pic' => 'tek-56000_597_main_large.png', 'sequence' => 0),
                        array('text' => 'DUFFEL TORBA 50L', 'longtext' => 'Kaj pa boš s tako veliko torbo na teku?  :)', 'pic' => 'tek-67050_535_main_large.png', 'sequence' => 0),
                        array('text' => 'UTILITY PARKA JOPIČ', 'longtext' => 'Ja, dobra izbira, če je zunaj temperatura -30°C... :)', 'pic' => 'tek-51379_990_main_large.png', 'sequence' => 0),
                        array('text' => 'LERWICK DEŽNI PLAŠČ', 'longtext' => 'Ta pa »dihta«! Znoj bo lil s tebe, pa še vse bo na tebi ostalo. :)', 'pic' => 'tek-62201_344_main_large.png', 'sequence' => 0),
                        array('text' => 'MISSION PARKA PLAŠČ', 'longtext' => 'Am... ti mogoče v Sibiriji tečeš…?', 'pic' => 'tek-62156_980_main_large.png', 'sequence' => 0),
                        array('text' => 'TREELINE HLAČE', 'longtext' => 'Smučarske hlače, zanimivo… smučke imaš tudi s seboj, ko tečeš? :)', 'pic' => 'tek-60334_813_main_large.png', 'sequence' => 0)
                    );
                shuffle($otherPics);
                $slices = array_slice($otherPics, 0, 3);
                $clothes = array(
                                array('text' => 'Challenger brezrokavnik', 'longtext' => 'Vrhunski tekaški brezrokavnik, ki je hkrati windstopper s prožnim hrbtnim delom.', 'pic' => 'tek-2.png', 'sequence' => 1),
                                array('text' => 'Logo kapa', 'longtext' => ' Z lasmi ali brez las, z LOGO KAPO pri teku vedno dobro izgledaš. <span style="font-weight: bold; color: #d72128;">Navadno si jo nadeneš, tik preden greš od doma.</span>', 'pic' => 'tek-3.png', 'sequence' => 2),
                                $slices[0],
                                $slices[1],
                                $slices[2]
                            );
                shuffle($clothes);
                return $layers = array( 'tagline'   => 'na teku',
                                        'taglineSuccess' => 'za tek.',
                                        'deg'       => 'twenty',
                                        'layers'    => $clothes
                                        );
                break;
            case "env-kolesarjenje":
                $otherPics = array(
                        array('text' => 'Lifa worm ODIN HYBRID MAJICA', 'longtext' => 'Te res tako zebe? Daj, malo poženi, da se ogreješ! :)', 'pic' => 'kolesarjenje-48205_689_main_large.png', 'sequence' => 0),
                        array('text' => 'HH TORBA S KOLESI', 'longtext' => 'Bravo! Ali greš okoli sveta z vso to prtljago? :)', 'pic' => 'kolesarjenje-67057_990_main_large.png', 'sequence' => 0),
                        array('text' => 'ALPHA JOPIČ', 'longtext' => 'Am... kaj zdaj delaš? Smučaš ali kolesariš? Ta jopič je vsekakor za smučanje. :)', 'pic' => 'kolesarjenje-62103_570_main_large.png', 'sequence' => 0),
                        array('text' => 'SALT JOPIČ', 'longtext' => 'To mora biti pa velika jadrnica, da se ti po njej s kolesom voziš v tej jadralni jakni. :)', 'pic' => 'kolesarjenje-31293_599_main_large.png', 'sequence' => 0),
                        array('text' => 'REPUBLIC JOPIČ', 'longtext' => 'Hm... greš s princem Harryjem na južni pol mogoče? :)', 'pic' => 'kolesarjenje-62158_570_main_large.png', 'sequence' => 0),
                        array('text' => 'HERITAGE PILE Velur', 'longtext' => 'O joj, videti si kot kosmatinec na kolesu. :)', 'pic' => 'kolesarjenje-51455_597_main_large.png', 'sequence' => 0),
                        array('text' => 'VICTOR CIS JOPIČ', 'longtext' => 'Greš na kolo ali se ti mudi na zmenek v mestu? :)', 'pic' => 'kolesarjenje-62079_515_main_large.png', 'sequence' => 0),
                        array('text' => 'ODIN SRAJCA', 'longtext' => 'S to srajco boš zvezda večera, ampak ne na kolesu. :)', 'pic' => 'kolesarjenje-50955_990_main_large.png', 'sequence' => 0)
                    );
                shuffle($otherPics);
                $slices = array_slice($otherPics, 0, 3);
                $clothes = array(
                                array('text' => 'AIRFOIL VETROVKA', 'longtext' => 'Tanka, zelo funkcionalna vetrovka, ki se lahko spremeni v brezrokavnik in je primerna za kolesarjenje in lahko pohodništvo.', 'pic' => 'kolesarjenje-2.png', 'sequence' => 1),
                                array('text' => 'HH ROKAVICE', 'longtext' => 'Vsestranske rokavice, ki jih lahko uporabljaš za jadranje, kolesarjenje in dvigovanje uteži. <span style="font-weight: bold; color: #d72128;">Navadno jih nadeneš na koncu.</span>', 'pic' => 'kolesarjenje-3.png', 'sequence' => 2),
                                $slices[0],
                                $slices[1],
                                $slices[2]
                            );
                shuffle($clothes);
                return $layers = array( 'tagline'   => 'na kolesarjenju',
                                        'taglineSuccess' => 'za kolesarjenje.',
                                        'deg'       => 'twenty',
                                        'layers'    => $clothes
                                        );
                break;
            case "env-pohodnistvo":
                 $otherPics = array(
                        array('text' => 'MESSENGER TORBA', 'longtext' => 'Za v hribe pa res ne potrebuješ računalnika. :)', 'pic' => 'pohodnistvo-67063_003_main_large.png', 'sequence' => 0),
                        array('text' => 'GRAPHIC MAJICA', 'longtext' => 'Bravo, pred 20 leti bi bila to dobra izbira. :)', 'pic' => 'pohodnistvo-51472_304_main_large.png', 'sequence' => 0),
                        array('text' => 'AALESUND FLOW PARKA JOPIČ', 'longtext' => 'Greš mogoče severne medvede obiskat? :)', 'pic' => 'pohodnistvo-51376_689_main_large.png', 'sequence' => 0),
                        array('text' => 'SANDVES SET - PU', 'longtext' => 'Ok, če greš gobe nabirat, ampak za pohodništvo pa to res ni prava izbira. :)', 'pic' => 'pohodnistvo-56733_300_main_large.png', 'sequence' => 0),
                        array('text' => 'DUBLIN DEŽNIK', 'longtext' => 'Raje vzemi nepremočljivo HH jakno, v njej bo gibanje bistveno lažje! :)', 'pic' => 'pohodnistvo-67890_990_main_large.png', 'sequence' => 0),
                        array('text' => 'ENIGMA JOPIČ', 'longtext' => 'Am... kje pa imaš smučke? Ta jopič je za smučanje. :)', 'pic' => 'pohodnistvo-62100_535_main_large.png', 'sequence' => 0),
                        array('text' => 'TRAVEL TROLLEY TORBA', 'longtext' => 'Se ti ne zdi, da je nahrbtnik laže prenašat kot pa to torbo?  :)', 'pic' => 'pohodnistvo-67056_990_main_large.png', 'sequence' => 0),
                        array('text' => 'GRAPHIC LS MAJICA', 'longtext' => 'Ti ne poznaš »švic« majice? :)', 'pic' => 'pohodnistvo-51471_949_main_large.png', 'sequence' => 0)
                    );
                shuffle($otherPics);
                $slices = array_slice($otherPics, 0, 3);
                $clothes = array(
                                array('text' => 'DAYBREAKER VELUR', 'longtext' => 'Veš, da je Helly Hansen izdelal prvi velur na svetu? Vsekakor prava izbira za čez športno perilo LIFA.', 'pic' => 'pohodnistvo-2.png', 'sequence' => 1),
                                array('text' => 'SEVEN J VETROVKA', 'longtext' => 'Tanka, klasična, nepremočljiva vetrovka, primerna tudi za pohodništvo! <span style="font-weight: bold; color: #d72128;">Oblečeš jo čez velur.</span>', 'pic' => 'pohodnistvo-3.png', 'sequence' => 2),
                                $slices[0],
                                $slices[1],
                                $slices[2]
                            );
                shuffle($clothes);
                return $layers = array( 'tagline'   => 'na pohodništvu',
                                        'taglineSuccess' => 'za pohodništvo.',
                                        'deg'       => 'ten',
                                        'layers'    => $clothes
                                        );
                break;
            case "env-sprehod":
                $otherPics = array(
                        array('text' => 'SKI TORBA', 'longtext' => 'Tega pa na sprehodu res ne potrebuješ... :)', 'pic' => 'sprehod-67047_162_main_large.png', 'sequence' => 0),
                        array('text' => 'DRY SUIT KOMBINEZON', 'longtext' => 'A mogoče snemaš nov James Bond film... :)', 'pic' => 'sprehod-31797_980_main_large.png', 'sequence' => 0),
                        array('text' => 'THRYM JOPIČ', 'longtext' => 'Pa saj nisi v St. Moritzu, da greš s tako jakno na sprehod. :)', 'pic' => 'sprehod-62145_841_main_large.png', 'sequence' => 0),
                        array('text' => 'LEGEND CHARGO HLAČE', 'longtext' => 'Priprave za na sprehod? V enem žepu sok, v drugem sendvič... pa kaj še? :)', 'pic' => 'sprehod-60361_535_main_large.png', 'sequence' => 0),
                        array('text' => 'RACING LIGHT OBLEKA', 'longtext' => 'Ok, če res nočeš, da se čisto vsi obrnejo za tabo, potem raje ne nosi tega. :)', 'pic' => 'sprehod-49064_990.png', 'sequence' => 0),
                        array('text' => 'ODIN MOUNTAIN HLAČE', 'longtext' => 'A si na sprehodu med Katmandujem in Annapurno? :)', 'pic' => 'sprehod-66570_980_main_large.png', 'sequence' => 0),
                        array('text' => 'TRAVEL TROLLEY TORBA', 'longtext' => 'Uf... to pa bo dolg sprehod! :)', 'pic' => 'sprehod-67056_990_main_large.png', 'sequence' => 0),
                        array('text' => 'PACE TRAINING HLAČE', 'longtext' => 'Tole te bo pa malo zeblo kaj? Kje imaš pa dolge hlače? :)', 'pic' => 'sprehod-49071_981_main_large.png', 'sequence' => 0)
                    );
                shuffle($otherPics);
                $slices = array_slice($otherPics, 0, 3);
                $clothes = array(
                                array('text' => 'GRAPHIC jopa s kapuco', 'longtext' => 'S to jopo boš vsekakor tarča številnih pogledov.', 'pic' => 'sprehod-2.png', 'sequence' => 1),
                                array('text' => 'PARAMOUNT SOFTSHELL BREZROKAVNIK:', 'longtext' => 'Klasičen moški brezrokavnik, primeren za prosti čas. <span style="font-weight: bold; color: #d72128;">Oblečeš ga čez jopo.</span>', 'pic' => 'sprehod-3.png', 'sequence' => 2),
                                $slices[0],
                                $slices[1],
                                $slices[2]
                            );
                shuffle($clothes);
                return $layers = array( 'tagline'   => 'na sprehodu',
                                        'taglineSuccess' => 'za sprehod.',
                                        'deg'       => 'fifteen',
                                        'layers'    => $clothes
                                        );
                break;
            default:
                return "no environment";
                break;
        }

    }

    public function checkNotificationsAction(Request $request, Application $app, $player)
    {

        $permissions = $app['facebook']->api('/me/permissions');
        if($permissions['data'][0]['manage_notifications']):
            $notifications = 1;
        else:
            $notifications = 0;
        endif;

        $dbnotifications =  $app['db']->fetchAssoc('SELECT notifications as val FROM users WHERE fbid = ?', array($player));

        if ( $notifications != $dbnotifications['val'] ):
            $app['db']->update('users', array('notifications' => $notifications), array('fbid' => $player));
            # echo "not the same! DB: " . $dbnotifications['val'] . " CURRENT: " . $notifications;
        else:
            # echo "the same! DB: " . $dbnotifications['val'] . " CURRENT: " . $notifications;
        endif;

    }

    public function fetchLeaderboardAction(Request $request, Application $app, $page) {

        $date = constant("setDate");
        // $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday this week", strtotime($date)));
        // $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        if(date('D', strtotime($date)) == "Sun"):
            $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday last week", strtotime($date)));
            $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        else:
            //echo "nope, not monday.";
            $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday this week", strtotime($date)));
            $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        endif;

        $limit = 20;
        $countAll = $app['db']->executeQuery("SELECT DISTINCT user_fbid FROM games WHERE timestamp BETWEEN '".$timestamp."' AND '".$timestamp1."'")->rowCount();
        $lastPage = ceil($countAll/$limit);
        $firstPage = 3;

        // echo "last page: " . $lastPage;

        $links[] = "«";
        if ($lastPage > 3) {
            // this specifies the range of pages we want to show in the middle
            $min = max($page - 2, 2);
            $max = min($page + 2, $lastPage-1);

            // we always show the first page
            $links[] = "1";

            // we're more than one space away from the beginning, so we need a separator
            if ($min > 2) {
                $links[] = "...";
            }

            // generate the middle numbers
            for ($i=$min; $i<$max+1; $i++) {
                if($i == $page) {
                    $links[] = "$i";
                } else {
                    $links[] = "$i";
                }
            }

            // we're more than one space away from the end, so we need a separator
            if ($max < $lastPage-1) {
                $links[] = "...";
            }
            // we always show the last page
            $links[] = "$lastPage";
        } else {
            // we must special-case three or less, because the above logic won't work
            if($lastPage == 0) {
                $links = array();
            } else if ($lastPage == 1) {
                $links = array("1");
            } else if ($lastPage == 2) {
                $links = array("1", "2");
            } else if ($lastPage == 3) {
                $links = array("1", "2", "3");
            }
            //$links = array("1", "2", "3");
        }
        if($lastPage == 0) {
            $links = array();
        } else if ($lastPage == 1) {
            $links = array("1");
        } else if ($lastPage == 2) {
            $links = array("1", "2");
        } else if ($lastPage == 3) {
            $links = array("1", "2", "3");
        } else {
            $links[] = "»";
        }
        // echo implode(" ", $links);
        // echo "<br />";
        // print_r($links);



        if($page > 0){
            $startCount = 20 * $page - $limit +1;
            $start = 20 * $page - $limit;
            //echo $start . "," . $limit;
        }

        $results = $app['db']->fetchAll("SELECT name as user_fbid, SUM(pointsGames) as totalPoints
                                         FROM games, users
                                         WHERE timestamp BETWEEN '".$timestamp."' AND '".$timestamp1."'
                                         AND users.fbid = games.user_fbid  GROUP BY user_fbid
                                         ORDER BY SUM(pointsGames) DESC LIMIT $start,$limit");

        // $results = $app['db']->fetchAll("SELECT name as user_fbid, SUM(pointsGames) as totalPoints
        //                                  FROM games, users WHERE users.fbid = games.user_fbid  GROUP BY user_fbid
        //                                  ORDER BY SUM(pointsGames) DESC LIMIT $start,$limit");

        return array('results' => $results, 'results2' => 0, 'startCount' => $startCount, 'pagination' => $links);

    }

    public function fetchLeaderboardActionAdmn(Request $request, Application $app, $page) {

        $date = constant("setDate");
        // $timestamp = date('Y-m-d H:i:s', strtotime("00:00:00 Monday this week", strtotime($date)));
        // $timestamp1 = date('Y-m-d H:i:s', strtotime("+1 week", strtotime($timestamp)));
        $timestamp = date('Y-m-d H:i:s', strtotime("2013-12-09 00:00:00", strtotime($date)));
        $timestamp1 = date('Y-m-d H:i:s', strtotime("2013-12-15 23:59:59", strtotime($timestamp)));

        echo "TIMESPAN: " . date('d.m.Y H:i:s', strtotime($timestamp)) . " - " . date('d.m.Y H:i:s', strtotime($timestamp1));

        $limit = 20;
        $countAll = $app['db']->executeQuery("SELECT DISTINCT user_fbid FROM games WHERE timestamp BETWEEN '".$timestamp."' AND '".$timestamp1."'")->rowCount();
        $lastPage = ceil($countAll/$limit);
        $firstPage = 3;

        // echo "last page: " . $lastPage;

        $links[] = "«";
        if ($lastPage > 3) {
            // this specifies the range of pages we want to show in the middle
            $min = max($page - 2, 2);
            $max = min($page + 2, $lastPage-1);

            // we always show the first page
            $links[] = "1";

            // we're more than one space away from the beginning, so we need a separator
            if ($min > 2) {
                $links[] = "...";
            }

            // generate the middle numbers
            for ($i=$min; $i<$max+1; $i++) {
                if($i == $page) {
                    $links[] = "$i";
                } else {
                    $links[] = "$i";
                }
            }

            // we're more than one space away from the end, so we need a separator
            if ($max < $lastPage-1) {
                $links[] = "...";
            }
            // we always show the last page
            $links[] = "$lastPage";
        } else {
            // we must special-case three or less, because the above logic won't work
            if($lastPage == 0) {
                $links = array();
            } else if ($lastPage == 1) {
                $links = array("1");
            } else if ($lastPage == 2) {
                $links = array("1", "2");
            } else if ($lastPage == 3) {
                $links = array("1", "2", "3");
            }
            //$links = array("1", "2", "3");
        }
        if($lastPage == 0) {
            $links = array();
        } else if ($lastPage == 1) {
            $links = array("1");
        } else if ($lastPage == 2) {
            $links = array("1", "2");
        } else if ($lastPage == 3) {
            $links = array("1", "2", "3");
        } else {
            $links[] = "»";
        }
        // echo implode(" ", $links);
        // echo "<br />";
        // print_r($links);



        if($page > 0){
            $startCount = 20 * $page - $limit +1;
            $start = 20 * $page - $limit;
            //echo $start . "," . $limit;
        }

        $results = $app['db']->fetchAll("SELECT name as user_fbid, SUM(pointsGames) as totalPoints
                                         FROM games, users
                                         WHERE timestamp BETWEEN '".$timestamp."' AND '".$timestamp1."'
                                         AND users.fbid = games.user_fbid  GROUP BY user_fbid
                                         ORDER BY SUM(pointsGames) DESC LIMIT $start,$limit");

        // $results = $app['db']->fetchAll("SELECT name as user_fbid, SUM(pointsGames) as totalPoints
        //                                  FROM games, users WHERE users.fbid = games.user_fbid  GROUP BY user_fbid
        //                                  ORDER BY SUM(pointsGames) DESC LIMIT $start,$limit");

        // return array('results' => $results, 'results2' => 0, 'startCount' => $startCount, 'pagination' => $links);

        return $app['twig']->render('leaderboard.twig', array('pagestyle' => 'env-smucanje', 'pagination' => $links, 'results' => $results, 'startCount' => $startCount, 'curPage' => 1));

    }

}

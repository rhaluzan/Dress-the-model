<?php
namespace notigo\Controllers;

use Silex\Application;
use notigo\Controllers\GameController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;

class IndexController
{

    public function indexAction(Request $request, Application $app)
    {

        $signedRequest = $app['facebook']->getSignedRequest();
        if($signed_request = $app['facebook']->parsePageSignedRequest())
        {
            if($signed_request->page->liked)
            {

                if($app['facebook']->getUser())
                {

                    try {
                        $user_profile = $app['facebook']->api('/me/permissions');
                        // Here : API call succeeded, you have a valid access token
                        if( $user_profile['data'][0]['email'] ) {

                            $game = new GameController();
                            if(!$game->checkPlayerAction($request, $app, $app['facebook']->getUser()))
                            {
                                $game->addPlayerAction($request, $app);
                            }

                            //$game->checkNotificationsAction($request, $app, $app['facebook']->getUser());

                            // if($game->checkIfPlayedAction($request, $app, $app['facebook']->getUser()))
                            // {

                            //     return $app['twig']->render('success.twig', array(  'pagestyle' => 'env-smucanje',
                            //                                                         'points' => $game->getPlayerPointsAction($request, $app, $app['facebook']->getUser()),
                            //                                                         'curpos' => $game->getPlayerRank($request, $app, $app['facebook']->getUser()),
                            //                                                         'allpos' => $game->getAllPlayers($request, $app)));
                            // } else {
                                return $app['twig']->render('environment.twig', array(  'pagestyle' => 'home',
                                                                                        'points' => $game->getPlayerPointsAction($request, $app, $app['facebook']->getUser()),
                                                                                        'curpos' => $game->getPlayerRank($request, $app, $app['facebook']->getUser()),
                                                                                        'allpos' => $game->getAllPlayers($request, $app)
                                                                                    ));
                            // }

                            //return $app['twig']->render('environment.twig', array('pagestyle' => 'home'));
                            // $environment = "env-smucanje";
                            // $game = new GameController();
                            // $layers = $game->fetchEnvironmentAction($request, $app, $environment);
                            // return $app['twig']->render('game.twig', array('pagestyle' => $environment, 'layers' => $layers));

                        } else {
                            return $app['twig']->render('likedNoAuth.twig');
                        }

                      } catch (FacebookApiException $e) {
                        // Here : API call failed, you don't have a valid access token
                        // you have to send him to $facebook->getLoginUrl()
                        $user = null;
                    }

                } else {
                    // return $app['twig']->render('likedNoAuth.twig');
                    return $app['twig']->render('environment.twig', array('pagestyle' => 'home'));
                }

            } else {
                return $app['twig']->render('notLiked.twig', array('pagestyle' => 'not-liked'));
            }

        }

    }


    public function environmentAction(Request $request, Application $app)
    {

        $environment = $request->get('env');

        $game = new GameController();

        $layers = $game->fetchEnvironmentAction($request, $app, $environment);

        return $app['twig']->render('game.twig', array( 'pagestyle'      => $environment,
                                                        'layers'         => $layers['layers'],
                                                        'tagline'        => $layers['tagline'],
                                                        'taglineSuccess' => $layers['taglineSuccess'],
                                                        'deg'            => $layers['deg'],
                                                        'points'         => $game->getPlayerPointsAction($request, $app, $app['facebook']->getUser())
                                                        )
                                    );

    }

    public function successAction(Request $request, Application $app)
    {

        $environment = $request->get('env');
        if(empty($environment)) {
            $environment = "env-smucanje";
        }

        $game = new GameController();

        $layers = $game->fetchEnvironmentAction($request, $app, $environment);
        return $app['twig']->render('success.twig', array('pagestyle'      => $environment,
                                                          'taglineSuccess' => $layers['taglineSuccess']
                                                          ));

    }

    public function leaderboardAction(Request $request, Application $app)
    {

        $page = $request->get('page');

        $game = new GameController();
        $test = $game->fetchLeaderboardAction($request, $app, $page);

        return $app['twig']->render('leaderboard.twig', array('pagestyle' => 'env-smucanje', 'pagination' => $test['pagination'], 'results' => $test['results'], 'startCount' => $test['startCount'], 'curPage' => $page));

    }

}

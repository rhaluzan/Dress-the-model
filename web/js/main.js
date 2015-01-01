// *****************
// Global variables
// *****************
var AppLocation = "https://www.facebook.com/pages/SPORT-SPIRIT/1427382827478573?id=1427382827478573&sk=app_163113757233003"
var points = 0;
var timer = 30;
var score = 0;
var gamePoints = 10;
var invitePoints = 1;
var loggedin = false;

Modernizr.load({
  test: Modernizr.mq('only all'),
  nope: 'https://haluzan.com/biz/app/GoClick/hh/sportspirit/web/js/respond.js'
});

playedGames();

// *****************
// Events
// *****************
var clicked1 = 0;

$('#login').on('touchstart click', function() {
    login();
})
$('#invite').on('touchstart click', function() {
    FB.ui({method: 'apprequests',
      message: 'Obleci modela in si pribori izdelke Helly Hansen!'
    }, function(response) {

            //console.log(response);
            var ids = response["to"];

            score = parseInt($('#score #value').text()) + ids.length;
            $('#score #value').text(score);

            var allids = new Array();
            for (var i = 0; i < ids.length; ++i)
            {
                allids.push(ids[i]);
                console.log(ids[i]);
            }

            if(allids)
            {
                console.log('ajax should run');
                $.ajax({
                    url: '//haluzan.com/biz/app/GoClick/hh/sportspirit/web/api/addInvites/'+JSON.stringify(allids),
                    context: document.body,
                    dataType: 'json'
                }).done(function(data) {
                    console.log(data);
                    if(data.status == "OK") {
                        console.log('OK: '+ data.msg);
                    }
                    else if(data.status == "ERROR") {
                        console.log('ERROR' + data.msg);
                    }
                    else {
                        console.log('default');
                    }
                });
            } else {
                console.log('no all ids');
            }

    });
});

$.each($('a:not([target])'), function() {
    $(this).removeAttr("href");
});

$('a:not([target])').on('touchstart click', function(event) {
    event.preventDefault();
    if($(this).find('.img-box').hasClass('locked')) {
        return false;
    } else if(loggedin) {
        window.location = 'https://haluzan.com/biz/app/GoClick/hh/sportspirit/web/'+$(this).attr('data-url');
    } else {
        if($('body').hasClass('home')) {
            login();
        }
    }

});

$('.items li').on('touchstart click', function() {
    var sequence = $(this).attr('data-sequence');
    if(sequence == 1 && clicked1 == 0) {
        clicked1 = 1;
        triggerGood();
    }
    else if(sequence == 2 && clicked1 == 1) {
        triggerGood();
        clicked1 = 2;
        //$('.wardrobe').hide();
        playedGames();
        $('#h1').hide();
        $('#layers').hide();
        //$('#position').removeClass('hidden');
        $('#successContainer').show();
        addGame($('body').attr('class'),score);
        //alert('correct - You won!');
        //$('#counter').countdown('pause')
    }
    else {
        triggerError();
    }
});

// *****************
// Hover tips
// *****************
$('ul.items > li').each(function() { // Grab all elements with a title attribute,and set "this"
    $(this).qtip({
         content: {
            // button: true
        },
        position: {
            my: 'center right',
            at: 'center left',
            adjust: {
                x: -16, y: 0, // Minor x/y adjustments
                mouse: true, // Follow mouse when using target:'mouse'
                resize: true, // Reposition on resize by default
                method: 'flip flip' // Requires Viewport plugin
            },
        },
        show: 'click',
        hide: 'unfocus',
        style: {
            classes: 'qtip-tipsy',
            tip: { // Requires Tips plugin
                corner: true, // Use position.my by default
                mimic: false, // Don't mimic a particular corner
                width: 20,
                height: 12,
                border: false, // Detect border from tooltip style
                offset: 0 // Do not apply an offset from corner
            }
        }
    });
});

// *****************
// Friend selector
// *****************
TDFriendSelector.init({debug: true});
fs = TDFriendSelector.newInstance({
    maxSelection: 5,
    friendsPerPage: 5,
    autoDeselection: true,
    callbackSubmit: function(selectedFriendIds) {
        console.log("The following friends were selected: " + selectedFriendIds.join(", "));
        // $('.inviteBox').html('<img src="http://graph.facebook.com/' + selectedFriendIds + '/picture?width=175&height=175" />');
        // $('#fbidinput').val(selectedFriendIds);
        inviteFriends(selectedFriendIds);
    }
});

// ****************
// Functions
// ****************
function login() {
    FB.login(function(response) {
        if (response.authResponse) {
            // The person logged into your app
            // console.log('logged in');
            top.window.location = AppLocation;
        } else {
            // The person cancelled the login dialog
            console.log('not logged in');
        }
    }, {scope: 'email, user_friends'});
}


// function inviteFriends(userids) {
//     console.log(userids);
//     for (var i in userids) {
//       //console.log(userids[i]);
//         FB.ui(
//         {
//             method: 'feed',
//             link: "https://apps.facebook.com/vzemite-si-cas-zase/",
//             name: 'name',
//             caption: "Vzemite si čas zase z vikend paketom v Termah Topolšica.",
//             description: "Nagrade so: Vikend najem apartmaja za 4 osebe, VIP zasebni wellness, masaža, celodnevno kopanje ... jah, sliši se čudovito! :)",
//             picture: "http://www.terme-topolsica.si/vzemi-si-cas-zase/images/share.png",
//             to: userids[i],
//             display : 'iframe'
//         },
//         function(response) {
//             if (response && response.post_id) {
//                 // dodaj, da zalebeži, če je objavil
//                 console.log('Post was published.');
//                 $('#qlform').submit()
//             } else {
//                 console.log('Post was not published.');
//                 return false;
//             }
//         });
//     }
// }


function getSelectedFriends() {
    return fs.getselectedFriendIds();
}

function triggerError() {
    if($('#error').css('display') == "none") {
        $('#error').show().fadeOut(4000);

    } else {
        $('#error').stop().fadeOut(0);
        triggerError();
    }
}
function triggerGood() {
    $('#good').show().fadeOut(3000);

    // Dress
    if($('.dress').hasClass('one')) {
        $('.dress').removeClass('one').addClass('two');
    }
    else if($('.dress').hasClass('two')) {
        $('.dress').removeClass('two').addClass('three');
    }

    // Layers
    if($('#layers .value').hasClass('one')) {
        $('#layers .value').removeClass('one').addClass('two');
    }
    else if($('#layers .value').hasClass('two')) {
        $('#layers .value').removeClass('two').addClass('three');
    }

    score = parseInt($('#score #value').text()) + gamePoints;
    $('#score #value').text(score);
}

function closeGame() {
    alert('Game Over!');
}

function addGame(env,number) {
    //console.log(env +  ' - ' + number)
    $.ajax({
        url: '//haluzan.com/biz/app/GoClick/hh/sportspirit/web/api/addGame/'+ env +'/'+number,
        context: document.body,
        dataType: 'json'
    }).done(function(data) {
        if(data.result) {
            console.log(data);
        } else {
            console.log(data);
        }
        window.top.location.href = AppLocation;
    });
}

function playedGames() {
    $.ajax({
        url: '//haluzan.com/biz/app/GoClick/hh/sportspirit/web/api/fetchPlayedEnvsActions',
        context: document.body,
        dataType: 'json'
    }).done(function(data) {
        if(data) {
            if(data.length >= 6) {
                $('#again').hide();
            }
            console.log(data);
            $.each(data, function (index, value) {
                console.log(value.environment);
                $('.img-box.'+value.environment).addClass('locked')
                $('.img-box.'+value.environment).append('<div class="locked"></div>');
            });
        }
    });
}

function checkUserAuth() {
    FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
            // the user is logged in and has authenticated your app
            console.log('user logged in to FB');
            loggedin = true;
        } else if (response.status === 'not_authorized') {
            // the user is logged in to Facebook, but has not authenticated your app
            console.log('app not auth');
        } else {
            // the user isn't logged in to Facebook.
            console.log('user not logged in to FB');
            login();
        }
    });
}

/*
startCounter(timer);
function startCounter(timer) {
    var countdown = timer;
    $('#counter').countdown({   until: countdown,
                                format: 'S',
                                compact: true,
                                onExpiry: closeGame
                            });
}
*/


// ****************
// Style stuff
// ****************
$('#h1').fitText(1, { minFontSize: '44px', maxFontSize: '44.45px' });

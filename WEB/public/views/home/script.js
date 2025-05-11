import {
    retrieveView
} from '../_/_common/modules/_/req.js';

retrieveView('home/main').then (
    function(view) {
        console.log(view);
    }
)
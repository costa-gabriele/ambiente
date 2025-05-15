import {
    retrieveView
} from "../_common/modules/_/req.js";

var serverElabData = {
    "list": [
        "lorem",
        "ipsum",
        "dolor"
    ],
    "multiList": [
        {"0": "A", "1": "B", "third": "C"},
        {"0": "D", "1": "E", "third": "F"}
    ]
};

var clientElabData = {
    "text": {
        "title": "This title is replaced client side!",
        "paragraph": "This paragraph too is handled by Javascript."
    }
};

retrieveView (
    "_/demo/main",
    serverElabData,
    clientElabData
).then (
    function(view) {
        console.log("Here's the view: " + view);
    }
);

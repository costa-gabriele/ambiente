import {
	WS_RETRIEVE_VIEW_ENDPOINT
} from "./config.js";


async function sendRequest(pEndPointURL, pData = {}, pMethod = "POST") {
	
	/*
	* The body of the request can be either multipart/form-data or application/json.
	* In the first case, pData is an instance of FormData, in the latter pData is an object.
	*/
	
	let headersList = {};
	let data = pData;
	
	if(!(pData instanceof FormData)) {
		headersList["Content-Type"] = "application/json";
		data = JSON.stringify(pData);
	}
	
	let responsePromise = new Promise (
		
		async function(fulfill, reject) {
			
			try {
				
				let response = await fetch (
					pEndPointURL.href,
					{
						headers: headersList,
						method: pMethod,
						body: data
					}
					
				);
				
				try {
					
					let responseData = response.text();
					fulfill(responseData);
					
				} catch(e) {
					
					// Request error
					reject(e);
					
				}
				
			} catch(e) {
				
				// Generic error
				reject(e);
				
			}
		
		}
		
	);
	
	return responsePromise;
	
}

function fillView(pViewTemplate, pViewData, pPrefix = "") {
	
	let view = pViewTemplate;
	
	for(let content in pViewData) {

		if(pViewData[content] && typeof pViewData[content] == "object") {
			view = fillView(view, pViewData[content], content + ".");
		} else {
			let contentKey = "{{" + pPrefix + content + "}}";
			let contentValue = pViewData[content];
			view = view.replaceAll(contentKey, contentValue);
		}
	}
	
	return view;
	
}

async function retrieveView(pViewName, pServerElabData = {}, pClientElabData = {}) {
		
	let viewData = {
		"viewName": pViewName,
		"viewValues": pServerElabData
	};
	
	return sendRequest(WS_RETRIEVE_VIEW_ENDPOINT, viewData).then (
		(viewTemplate) => {
			return fillView(viewTemplate, pClientElabData);
		}
	);
	
}

export {
	sendRequest,
	fillView,
	retrieveView
}

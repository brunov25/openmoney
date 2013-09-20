//curl -u username:password http://cyclos.proudleo.com:8080/cyclos/rest/access/initialData
{"profile":
	{"id":2,
	 "name":"Dominique Legault",
	 "username":"deefactorial",
	 "email":"deefactorial@gmail.com",
	 "customValues":[{"internalName":"gender",
		 			  "fieldId":2,
		 			  "displayName":"Gender",
		 			  "value":"Male",
		 			  "possibleValueId":1},
		 			 {"internalName":"address",
		 			  "fieldId":3,
		 			  "displayName":"Address",
		 			  "value":"1234 my Street"},
		 			 {"internalName":"postalCode",
		 			  "fieldId":4,
		 			  "displayName":"Postal code",
		 			  "value":"X0X 0X0"},
		 			 {"internalName":"city",
		 			  "fieldId":5,
		 			  "displayName":"City",
		 			  "value":"Victoria"}]},
	  "requireTransactionPassword":false,
	  "accounts":[{"id":17,
		           "type":{"id":10,
		        	       "name":"Business Time Account",
		        	       "currency":{"id":2,
		        	    	           "symbol":"hrs",
		        	    	           "name":"Hours of Time"}},
		           "default":false},
		          {"id":5,
		           "type":{"id":5,
		        	       "name":"Member account",
		        	       "currency":{"id":1,
		        	    	           "symbol":"units",
		        	    	           "name":"Units"}},
		           "default":false},
		          {"id":14,
		           "type":{"id":9,
		        	       "name":"Personal Bulkly Bucks",
		        	       "currency":{"id":6,
		        	    	           "symbol":"bb",
		        	    	           "name":"Bulkly Bucks"}},
		           "default":false},
		           {"id":11,
		        	"type":{"id":8,"name":"Personal Community Way","currency":{"id":3,"symbol":"cw","name":"Community Way"}},
		        	"default":false},
		           {"id":8,
		        	"type":{"id":7,"name":"Personal Time Account","currency":{"id":2,"symbol":"hrs","name":"Hours of Time"}},
		        	"default":true}],
		"canMakeMemberPayments":true,
		"canMakeSystemPayments":true,
		"decimalCount":2,
		"decimalSeparator":"."}
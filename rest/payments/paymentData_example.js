curl -u deefactorial:password 'http://openmoney.proudleo.com:8080/cyclos/rest/payments/paymentData?destination=MEMBER&toMemberId=4'
{"transferTypes":[{"id":33,
	"name":"b2b hrs exchange",
	"from":{"id":10,
		"name":"Business Time Account",
		"currency":{"id":2,
			"symbol":"hrs",
			"name":"Hours of Time"}},
	"to":{"id":10,
		"name":"Business Time Account",
		"currency":{"id":2,
			"symbol":"hrs",
			"name":"Hours of Time"}}},
	{"id":34,
	 "name":"b2p hrs exchange",
	 "from":{"id":10,
		 "name":"Business Time Account",
		 "currency":{"id":2,
			 "symbol":"hrs",
			 "name":"Hours of Time"}},
	 "to":{"id":7,
		 "name":"Personal Time Account",
		 "currency":{"id":2,
			 "symbol":"hrs",
			 "name":"Hours of Time"}}},
	{"id":32,
	 "name":"p2b hrs exchange",
	 "from":{"id":7,
		 "name":"Personal Time Account",
		 "currency":{"id":2,
			"symbol":"hrs",
			"name":"Hours of Time"}},
	 "to":{"id":10,
		 "name":"Business Time Account",
		 "currency":{"id":2,
			 "symbol":"hrs",
			 "name":"Hours of Time"}}},
	{"id":31,
		 "name":"p2p hrs exchange",
		 "from":{"id":7,
			 "name":"Personal Time Account",
			 "currency":{"id":2,
				 "symbol":"hrs",
				 "name":"Hours of Time"}},
		 "to":{"id":7,
			 "name":"Personal Time Account",
			 "currency":{"id":2,
				 "symbol":"hrs",
				 "name":"Hours of Time"}}},
	{"id":29,
		"name":"Trade transfer from mobile",
		"from":{"id":5,
			"name":"Member account",
			"currency":{"id":1,
				"symbol":"units",
				"name":"Units"}},
		"to":{"id":5,
			"name":"Member account",
			"currency":{"id":1,
				"symbol":"units",
				"name":"Units"}}}],
	"accountsStatus":{
		"5":{"balance":-16.000000,
			"formattedBalance":"-16.00 units",
			"availableBalance":999984.000000,
			"formattedAvailableBalance":"999,984.00 units",
			"reservedAmount":0.000000,
			"formattedReservedAmount":"0.00 units",
			"creditLimit":1000000.000000,
			"formattedCreditLimit":"1,000,000.00 units"},
		"7":{"balance":0,
			"formattedBalance":"0.00 hrs",
			"availableBalance":1000000.000000,
			"formattedAvailableBalance":"1,000,000.00 hrs",
			"reservedAmount":0,
			"formattedReservedAmount":"0.00 hrs",
			"creditLimit":1000000.000000,
			"formattedCreditLimit":"1,000,000.00 hrs"},
		"10":{"balance":4.000000,
			"formattedBalance":"4.00 hrs",
			"availableBalance":1000004.000000,
			"formattedAvailableBalance":"1,000,004.00 hrs",
			"reservedAmount":0.000000,
			"formattedReservedAmount":"0.00 hrs",
			"creditLimit":1000000.000000,
			"formattedCreditLimit":"1,000,000.00 hrs"}},
	"toMember":{"id":4,
		"name":"json Doe",
		"username":"json",
		"email":"json@example.com"}}

curl -u deefactorial:password 'http://openmoney.proudleo.com/rest/payments/paymentData?destination=MEMBER&toMemberId=444'
{"transferTypes":[{"id":"1",
	"name":"cc Trade",
	"from":{"id":"1",
		"symobl":"cc",
		"name":"cc"},
	"to":{"id":"1",
		"symobl":"cc",
		"name":"cc"}}],
"accountsStatus":{
	"542":{"balance":-12,
		"formattedBalance":"-12.00 cc",
		"availableBalance":0,
		"formattedAvailableBalance":"0.00 cc",
		"reservedAmount":0,
		"formattedReservedAmount":"0.00 cc",
		"creditLimit":0,
		"formattedCreditLimit":"0.00 cc"}},
"toMember":{"id":"444",
	"name":"Dominique Legault",
	"username":"dom",
	"email":"deefactorial+1@gmail.com"}}
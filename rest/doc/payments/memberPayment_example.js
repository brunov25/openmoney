curl -u deefactorial:password -i -H "Content-Type: application/json" -X POST -d '{"toMemberId":4,"amount":10.000000,"transferTypeId":29}' 'http://openmoney.proudleo.com:8080/cyclos/rest/payments/memberPayment'
HTTP/1.1 200 OK
Server: Apache-Coyote/1.1
Cache-control: no-cache, no-store, must-revalidate
Content-Type: application/json;charset=UTF-8
Content-Length: 521
Date: Mon, 16 Sep 2013 21:21:19 GMT

{"wouldRequireAuthorization":false,
	"from":{"id":2,
		"name":"Dominique Legault",
		"username":"deefactorial",
		"email":"deefactorial@gmail.com"},
	"to":{"id":4,"name":"json",
		"username":"json",
		"email":"json@example.com"},
	"finalAmount":10.00,
	"formattedFinalAmount":"10.00 units",
	"transferType":{"id":29,
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
				"name":"Units"}}}}

//openmoney response
{"wouldRequireAuthorization":false,
	"from":{"id":"443",
		"name":"Dominique Legault",
		"username":"deefactorial",
		"email":"deefactorial@gmail.com"},
	"to":{"id":"444",
		"name":"Dominique Legault",
		"username":"dom",
		"email":"deefactorial+1@gmail.com"},
	"finalAmount":"10.00",
	"formattedFinalAmount":"10.00 cc",
	"transferType":{"id":"1",
		"name":"cc Trade",
		"from":{"id":"542",
			"name":"deefactorial",
			"currency":{"id":"1",
				"symbol":"cc",
				"name":"cc"}},
		"to":{"id":"544",
			"name":"dom",
			"currency":{"id":"1",
				"symbol":"cc",
				"name":"cc"}}}}
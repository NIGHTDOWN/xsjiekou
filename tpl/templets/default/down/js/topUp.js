$(function(){
	$("#cardBtn")
	.click(
			function() {

				var formParamArray = $(
						"form")
						.serializeArray();
				var o = {};
				$.each(
								formParamArray,
								function() {

									if (o[this.name] !== undefined) {
										if (!o[this.name].push) {
											o[this.name] = [ o[this.name] ];
										}
										o[this.name]
												.push(this.value
														|| '');
									} else {
										o[this.name] = this.value
												|| '';
									}
								});
				
				
				if(o.cardNum==""|| (o.cardId=="" &&  (o.vendor==undefined   || o.vendor!="vietnamMGC" )) || o.vendor==""){
					var msg="";
					if(o.countryName=="vietnam"){
						if(o.vendor==undefined ||o.vendor==""){
							msg="Vui lòng chọn một nhà cung cấp";
							
						}else if(o.cardNum==""){
							msg="Mã thẻ không được để trống";
						}else if(o.cardId==""){
							msg="Số Seri không được để trống";
						}
						
					
					}else if(o.countryName=="thailand"){
						if(o.vendor==undefined ||o.vendor==""){
							msg="โปรดเลือกผู้ให้บริการ";
						}else if(o.cardNum==""){
							msg="PIN ต้องไม่ว่างเปล่า";
						}else if(o.cardId==""){
							msg="PIN ต้องไม่ว่างเปล่า";
						}
					}
					alert(msg);
//					$("#errCause").text(payfail+":"+msg);
//				    $("#errCause").attr("data",msg);
//				$("#topUp").hide();
//				$("#deal_box").hide();
//				$("#topError").show();
				return;
				}else{
					if(o.countryName=="vietnam"){
						
						if(( o.vendor=="vietnamVMS" || o.vendor=="vietnamVNP"|| o.vendor=="vietnamVTT"  ) 
								&& o.cardPrintAmount=="" ){
							alert('Vui lòng chọn mệnh giá thẻ');
							$("#cardPrintAmount").css('border-color','#FF0000');
							return;
						}
						
//						var reg1 = /^[0-9]*$/;
//						if(!reg1.test(o.cardNum)){
//							alert("M茫 th岷籭 膼峄媙h d岷g sai,V铆 d峄�: 1234567890");
//						}
//
//						var reg = /^[a-zA-Z][a-zA-Z][0-9]*$/;
//						if(!reg.test(o.cardId)){
//							alert("S峄� Seri 膼峄媙h d岷g sai,V铆 d峄�: CB01234567");
//							//return;
//						}
//						
					}
				}
				btnChange();
				/* json鏍煎紡鐨� $.ajax({
				 	type:"POST",
				 	url:$("form").attr("action"),
				 	contentType: "application/json; charset=utf-8",
				 	data:JSON.stringify(o),
				 	dataType:"json",
				 	success:function(message){
				 		if("200"==message.result){
				 			alert("璇ョ瑪璁㈠崟浜ゆ槗鎴愬姛,璁㈠崟淇℃伅鏄�:"+message.serialNumber);
				 		}else if("500"==message.result){
				 			alert("鍙戠敓閿欒锛岄敊璇槸"+message.error);
				 		}
				 	}
				 	
				 }); */
				$.post(
								$("form")
										.attr(
												"action"),
								$("form")
										.serialize(),
								function(
										message) {
									alert(message);return false;
									var message = JSON.parse(message);
									
									$("#errCause").attr("data","");   /* 娓呯┖涔嬪墠鐨勬暟鎹� */
									if ("200" == message.result && (message.price!=undefined && message.price>0 || "SUBMITTED_SUCCESSFULLY"== message.error )) {
										var cpInquiryUrl=  message.errorUrl;
										/*alert(rootPath+"/toDiffCountry/toWebPaySuccessPage?orderId="+orderId+"&errorMsg="+(("SUBMITTED_SUCCESSFULLY"== message.error)?"SUBMITTED_SUCCESSFULLY":(message.price/100))
												+"&goodsKey="+encodeURIComponent(encodeURIComponent(goodsKey))+"&cpInquiryUrl="+cpInquiryUrl);*/
										window.location.href=rootPath+"/home/recharge/PaySuccess?orderId="+orderId+"&errorMsg="+(("SUBMITTED_SUCCESSFULLY"== message.error)?"SUBMITTED_SUCCESSFULLY":(message.price/100))
										+"&goodsKey="+encodeURIComponent(encodeURIComponent(goodsKey))+"&cpInquiryUrl="+cpInquiryUrl;
									} else  {
//										if("504" == message.result || "502" == message.result){
										if(message.isLock!="no"){
											var cpInquiryUrl = message.errorUrl;
											window.location.href=rootPath+"/home/recharge/PayError?errorMsg="+encodeURIComponent(encodeURIComponent(((message.errorDescr==undefined||message.errorDescr==""))?message.error:""+message.errorDescr))+"&orderId="+orderId
											+"&goodsKey="+encodeURIComponent(encodeURIComponent(goodsKey))+"&cpInquiryUrl="+cpInquiryUrl;
										
										}else{
											btnChange();
											/* alert("鍙戠敓閿欒锛岄敊璇槸"
													+ message.error); */
											$("#errCause").text(payfail+":"+message.error+((message.errorDescr==undefined)?"":""+message.errorDescr));
										    $("#errCause").attr("data",message.errorUrl);
											$("#topUp").hide();
											$("#deal_box").hide();
											$("#topError").show();
										}
									}

								});

			});
	
	
	
	function btnChange(){
		var read= $("#cardBtn").attr("disabled");
		
		if(!read){
			$("#cardBtn").css("filter","brightness(0.8)");
			$("#cardBtn").attr("disabled","true");
			$("#cardBtn").val(paying);
			$("#deal_box").show();
		}else{
			$("#cardBtn").removeAttr("disabled");
			$("#cardBtn").css("filter","");

			$("#cardBtn").val(confirmpay);
		}
	}
	
	
	
	
	
	
	
	
	
});
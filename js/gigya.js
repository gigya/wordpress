// Gigya Namespace
window.Gigya = {};

jQuery(document).ready(function($) {
	// Template Manager
	Gigya.Tmpl = function(target){
		this.target = target;
		return this;
	};
	
	Gigya.Tmpl.prototype = {
		target : null,
		renderLoginDialog: function(data){
			this.header(data).newUser(data);
			if(data.linkAccount) this.linkAccount(data);
			$('#gigya-new-user-wrap').wrap('<div class="ui-helper-clearfix" style="margin-bottom:15px"/>').parent()
			.append($('#gigya-sep-wrap'))
			.append($('#gigya-account-linking-wrap'));
		},
		tmpl : function(tplId,params,onAppend){
			$("#"+tplId).tmpl(params || {}).appendTo(this.target);
			if(onAppend) onAppend.call(this,params);
			return this;	
		},
		newUser: function(params){
			this.tmpl("gigya-new-user-tmpl",params,function(){
				$("#gigya-new-user-button").click(function(){
					Gigya.Ajax.login({
						actionType : "register-email", 
						email      : $("#gigya-new-user-wrap input[name=email]").val()
					});
				});
			});
			return this;
		},
		linkAccount: function(params){
			this.tmpl("gigya-account-linking-tmpl",params,function(){
				$("#gigya-new-account-button").click(function(){
					Gigya.Ajax.login({
						actionType : "link-account", 
						email      : $("#gigya-account-linking-wrap input[name=email]").val(),
						password   : $("#gigya-account-linking-wrap input[name=password]").val()
					});
				});
			});
			return this;
		},
		header: function(params){
			this.tmpl("gigya-header-tmpl",params,$.noop);
			return this;
		}
	}
	// Error Manager
	Gigya.Error =  function(){
		return {
			show: function(text){
				try {
					this.hide();
					var show_in_dialog = $("#dialog-header").length>0,target,markup;
					if(show_in_dialog){
						target = $("#dialog-header");
						markup = "<p class='message error' style='display:none;'>"+text+"<br></p>";
					} else {
						target =  $("#login h1");
						markup = "<div id='login_error'>"+text+"<br></div>";
					}
					
					target.after(markup).parent().find("p.message").fadeIn();
				} catch(e) {
					
				}
				 
			},
			hide: function(){
				try {
					var show_in_dialog = $("#dialog-header").length>0,target,markup;
					var target = $("#dialog-header").length>0 ? $("#dialog-header").parent().find(".message") :  $("#login_error,p.message");
					target.remove();
				} catch(e) {
					
				}
			}
		}
	}();
	// Login Ajax
	Gigya.Ajax = function(){
		var userObject = {};
		var adminurl = "";
		return {
			onSignIn: $.noop,
			setUrl: function(url){
				adminurl = url;
			},
			setUserObject: function(obj){
				userObject = obj;
				if(userObject.user.firstName == "") userObject.user.firstName = userObject.user.nickname;
			},
			login: function(data){
				var that = this;
				Gigya.Error.hide();
				$.post(adminurl,$.extend({
					userObject:JSON.stringify(userObject),
					action: "gigya_user_login"
				},data),function(r){
					try {
						var r = JSON.parse(r);
						switch(r.type) {
							case "email_exist":
								new Gigya.Dialog().open(function(){
									new Gigya.Tmpl(this).renderLoginDialog($.extend(userObject,{
										isEmailExist  :true,
										linkAccount   : (r.params.account_linking && r.params.force_email) 
									}));
										
								});
							break; 
							case "new_user_email_required":
								new Gigya.Dialog().open(function(){
									new Gigya.Tmpl(this).renderLoginDialog($.extend(userObject,{
										isNewUser    :true,
										isEmailExist :false,
										linkAccount  : (r.params.account_linking && r.params.force_email) 
									}));	
								});	
							break;
	 
							case "error":
							Gigya.Error.show(r.text);
							break; 
							case "signin":
								that.onSignIn.call(this,r,userObject);
							break;
						}
					} catch(e) {
			
					}
				});
			}
		}
	}();
	
	// Dialog
	Gigya.Dialog = function(){
		if($("#login-dialog").length==0) $("body").append("<div id='login-dialog'></div>");
		this.target = $("#login-dialog");
		return this;
	};
	 
	Gigya.Dialog.prototype = {
		open: function(beforeOpen){
			var that = this,beforeOpen = beforeOpen || $.noop;
			// render markup for dialog before open
			that.target.empty();
			beforeOpen.call(that.target);
			// open dialog
			$("#login-dialog").dialog($.extend({
				title: "Login",
				modal: true,
				width:'auto',
				resizable: false,
				close: function(){
					that.target.empty();
					$(this).dialog("destroy");	
				}
			},{})).dialog("open");		
		}
	};
});
	
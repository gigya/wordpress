// Gigya Namespace
window.Gigya = {};

jQuery(document).ready(function (jQuery) {
  gigya.socialize.addEventHandlers({
    onLogin: function (userObject) {
      Gigya.Ajax.setUserObject(userObject);
      Gigya.Ajax.onSignIn = function (r) {
        window.document.location.href = r.params.url;
      };
      Gigya.Ajax.login();
    }
  });
  // Template Manager
  jQuery.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    jQuery.each(a, function () {
      if (o[this.name]) {
        if (!o[this.name].push) {
          o[this.name] = [o[this.name]];
        }
        o[this.name].push(this.value || '');
      } else {
        o[this.name] = this.value || '';
      }
    });
    return o;
  };
  Gigya.Tmpl = function (target) {
    this.target = target;
    return this;
  };

  Gigya.Tmpl.prototype = {
    target: null,
    renderLoginDialog: function (data) {
      this.header(data).newUser(data);
      if (data.linkAccount) this.linkAccount(data);
      jQuery('#gigya-new-user-wrap').wrap('<div class="ui-helper-clearfix" style="margin-bottom:15px"/>').parent()
          .append(jQuery('#gigya-sep-wrap'))
          .append(jQuery('#gigya-account-linking-wrap'));
    },
    tmpl: function (tplId, params, onAppend) {
      jQuery("#" + tplId).tmpl(params || {}).appendTo(this.target);
      if (onAppend) onAppend.call(this, params);
      return this;
    },
    newUser: function (params) {
      this.tmpl("gigya-new-user-tmpl", params, function () {
        jQuery("#gigya-new-user-button").click(function () {
          Gigya.Ajax.login({
            actionType: "register-email",
            email: jQuery("#gigya-new-user-wrap input[name=email]").val()
          });
        });
      });
      return this;
    },
    linkAccount: function (params) {
      this.tmpl("gigya-account-linking-tmpl", params, function () {
        jQuery("#gigya-new-account-button").click(function () {
          Gigya.Ajax.login({
            actionType: "link-account",
            email: jQuery("#gigya-account-linking-wrap input[name=email]").val(),
            password: jQuery("#gigya-account-linking-wrap input[name=password]").val()
          });
        });
      });
      return this;
    },
    header: function (params) {
      this.tmpl("gigya-header-tmpl", params, jQuery.noop);
      return this;
    },
    renderForm: function (params) {
      jQuery('#login-dialog').html(params.html);
      jQuery('#gigya-ajax-submit').click(function (e) {
        e.preventDefault();
        Gigya.Ajax.login({
          actionType: "moreInfo",
          info: jQuery(this).parents('form').eq(0).serializeObject()
        });

      });
    }
  };
  // Error Manager
  Gigya.Error = function () {
    return {
      show: function (text) {
        try {
          this.hide();
          var show_in_dialog = jQuery("#dialog-header").length > 0, target, markup;
          if (show_in_dialog) {
            target = jQuery("#dialog-header");
            markup = "<p class='message error' style='display:none;'>" + text + "<br></p>";
          } else {
            target = jQuery("#login h1");
            markup = "<div id='login_error'>" + text + "<br></div>";
          }
          if (target.length == 0) {
            jQuery('.error-message').html(text);
            return false;
          }

          target.after(markup).parent().find("p.message").fadeIn();
        } catch (e) {
          console.log(e);

        }

      },
      hide: function () {
        try {
          var show_in_dialog = jQuery("#dialog-header").length > 0, target, markup;
          var target = jQuery("#dialog-header").length > 0 ? jQuery("#dialog-header").parent().find(".message") : jQuery("#login_error,p.message");
          target.remove();
        } catch (e) {

        }
      }
    }
  }();
  // Login Ajax
  Gigya.Ajax = function () {
    var userObject = {};
    var adminurl = (typeof gigyaVars !== 'undefined') ? gigyaVars.adminurl : "";
    return {
      onSignIn: jQuery.noop,
      setUrl: function (url) {
        adminurl = url;
      },
      setUserObject: function (obj) {
        userObject = obj;
        if (userObject.user.firstName == "") userObject.user.firstName = userObject.user.nickname;
      },
      login: function (data) {
        var that = this;
        Gigya.Error.hide();
        if (adminurl == "" && typeof gigyaVars !== 'undefined') {
          adminurl = gigyaVars.adminurl;
        }
        jQuery.post(adminurl, jQuery.extend({
          userObject: JSON.stringify(userObject),
          action: "gigya_user_login",
          step: 1
        }, data), function (r) {
          //try {
          //var r = JSON.parse(r);
          switch (r.data.type) {
            case "email_exist":
              new Gigya.Dialog().open(function () {
                new Gigya.Tmpl(this).renderLoginDialog(jQuery.extend(userObject, {
                  isEmailExist: true,
                  linkAccount: (r.data.params.account_linking && r.data.params.force_email)
                }));

              });
              break;
            case "new_user_email_required":
              new Gigya.Dialog().open(function () {
                new Gigya.Tmpl(this).renderLoginDialog(jQuery.extend(userObject, {
                  isNewUser: true,
                  isEmailExist: false,
                  linkAccount: (r.data.params.account_linking && r.data.params.force_email)
                }));
              });
              break;
            case "reg_form":
              new Gigya.Dialog().open(function () {
                new Gigya.Tmpl(this).renderForm(jQuery.extend({}, r.data, userObject));
              });
              break;
            case "error":
              Gigya.Error.show(r.data.text);
              break;
            case "signin":
              that.onSignIn.call(this, r.data, userObject);
              break;
          }
          //} catch (e) {

          //}
        });
      }
    }
  }();

  // Dialog
  Gigya.Dialog = function () {
    if (jQuery("#login-dialog").length == 0) jQuery("body").append("<div id='login-dialog'></div>");
    this.target = jQuery("#login-dialog");
    return this;
  };

  Gigya.Dialog.prototype = {
    open: function (beforeOpen) {
      var that = this, beforeOpen = beforeOpen || jQuery.noop;
      // render markup for dialog before open
      that.target.empty();
      beforeOpen.call(that.target);
      // open dialog
      jQuery("#login-dialog").dialog(jQuery.extend({
        title: "Login",
        modal: true,
        width: 'auto',
        resizable: false,
        zIndex: 100000,
        close: function () {
          that.target.empty();
          jQuery(this).dialog("destroy");
        },
        buttons: {
          Cancel: function () {
            jQuery(this).dialog("close");
          }
        }
      }, {})).dialog("open");

      ;
    }
  };
});
	
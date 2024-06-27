// popupmanager (frontend)
// v1.0.0


/*! js-cookie v3.0.5 | MIT | https://github.com/js-cookie/js-cookie */
!function (t, n) { "object" == typeof exports && "undefined" != typeof module ? module.exports = n() : "function" == typeof define && define.amd ? define(n) : (t = "undefined" != typeof globalThis ? globalThis : t || self, function () { var e = t.Cookies, exports = t.Cookies = n(); exports.noConflict = function () { t.Cookies = e; return exports } }()) }(this, function () { "use strict"; function c(e) { for (var t = 1; t < arguments.length; t++) { var n, o = arguments[t]; for (n in o) e[n] = o[n] } return e } return function t(u, i) { function n(e, t, n) { if ("undefined" != typeof document) { "number" == typeof (n = c({}, i, n)).expires && (n.expires = new Date(Date.now() + 864e5 * n.expires)); n.expires && (n.expires = n.expires.toUTCString()); e = encodeURIComponent(e).replace(/%(2[346B]|5E|60|7C)/g, decodeURIComponent).replace(/[()]/g, escape); var o, r = ""; for (o in n) if (n[o]) { r += "; " + o; !0 !== n[o] && (r += "=" + n[o].split(";")[0]) } return document.cookie = e + "=" + u.write(t, e) + r } } return Object.create({ set: n, get: function (e) { if ("undefined" != typeof document && (!arguments.length || e)) { for (var t = document.cookie ? document.cookie.split("; ") : [], n = {}, o = 0; o < t.length; o++) { var r = t[o].split("="), i = r.slice(1).join("="); try { var c = decodeURIComponent(r[0]); n[c] = u.read(i, c); if (e === c) break } catch (e) { } } return e ? n[e] : n } }, remove: function (e, t) { n(e, "", c({}, t, { expires: -1 })) }, withAttributes: function (e) { return t(this.converter, c({}, this.attributes, e)) }, withConverter: function (e) { return t(c({}, this.converter, e), this.attributes) } }, { attributes: { value: Object.freeze(i) }, converter: { value: Object.freeze(u) } }) }({ read: function (e) { return (e = '"' === e[0] ? e.slice(1, -1) : e).replace(/(%[\dA-F]{2})+/gi, decodeURIComponent) }, write: function (e) { return encodeURIComponent(e).replace(/%(2[346BF]|3[AC-F]|40|5[BDE]|60|7[BCD])/g, decodeURIComponent) } }, { path: "/" }) });


/* ------------------------------------------------------------------------------------------------------------ */


$(function(){

	$('.pm-popup').each(function(){
		var breakpoint = 920;							//Breakpoint (px) Desktop <> Mobile
		
		var pop = $(this);
		var coid = 'popup_manager_' + pop.attr('id');
		
		//Padding setzen		
		$(window).on('load resize', function(){
			p 	= parseInt(pop.data('padding'));
			pm 	= parseInt(pop.data('paddingm'));
			cnt = pop.find('.pmp-content');
			
			if ($(window).width() > breakpoint) { if (!isNaN(p)) { cnt.css({ 'padding': p+'px' }); } } 
			else { if (!isNaN(pm)) { cnt.css('padding', pm+'px'); } }
		});
		
		//Close anbinden
		var cl = pop.find('.pmp-close');
			cl.click(function(){
				pop.removeClass('isopened').addClass('isclosed'); $('html').removeClass('pm-popup-opened');
				
				//Close-Cookie setzen
				c = parseInt(pop.data('cookie')); if (isNaN(c)) c = 0;
				if (c !== 'undefined' && c > 0 && typeof coid !== 'undefined' && coid != "") {
					Cookies.set(coid, '1', { expires: c, path: '/', sameSite: 'Lax' });
				}
			})
		pop.find('.pmp-overlay, .pmp-overlay-blur').click(function(){ cl.trigger('click'); });
		
		//Popup jetzt einblenden
		$(window).on('load resize', function(){
			if (!pop.hasClass('isclosed') && ( ($(window).width() > breakpoint && pop.hasClass('only-desktop')) || ($(window).width() <= breakpoint && pop.hasClass('only-mobile')) || (!pop.hasClass('only-desktop') && !pop.hasClass('only-mobile')) )) {					 
				d = parseInt(pop.data('delay')); if (isNaN(d)) d = 0; d = d * 1000;		
				if (typeof Cookies.get(coid) === 'undefined') {
					setTimeout(function(){
						if (!pop.hasClass('isclosed')) {
							//BodyScrollbar setzen
							if (pop.data('bodyscrollbar') == 'hide') { $('body').addClass('pm-popup-noscroll'); }
						 
							//Popup anzeigen
							$('html').addClass('pm-popup-opened');
							pop.addClass('isopened');
							pop.attr('tabindex', '0');
							pop.focus();
						}
					}, d);
				} else { pop.remove(); }
			}
		});
	});
	
});
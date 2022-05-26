'use strict';

/**
 * Creates a Beacon object with given params.
 *
 * @param {string} beaconId Id of the beacon from HelpScout.
 * @param {string} confirmationMessage Message shown to user to get his confirmation for Beacon initialization.
 * @constructor
 */
function HsBeacon(beaconId, confirmationMessage) {
	this.beaconId = beaconId;
	this.confirmationMessage = confirmationMessage;
}



/**
 * HelpScout Beacon implementation. Can ask for permission before initialize&show.
 */
HsBeacon.prototype = {
	initialized: false,
	confirmationMessage: '',
	beaconId: '',

	/**
	 * Attach ask&show event to given class.
	 *
	 * @param {string} buttonClass
	 */
	attachBeaconEvents: function (buttonClass) {
		const self = this;
		jQuery('.' + buttonClass).click(function () {
			jQuery(this).blur();
			if (self.showBeaconIfConfirmed()) {
				jQuery(this).fadeOut("slow");
			}
		})
	},

	showBeaconIfConfirmed: function () {
		let wantBeaconRun = confirm(this.confirmationMessage);
		if (wantBeaconRun) {
			this.ensureBeaconInitialization();
			this.beaconShow();
		}
		return wantBeaconRun;
	},

	ensureBeaconInitialization: function () {
		if (!this.initialized) {
			this.initialized = true;
			!function (e, t, n) {
				function a() {
					var e = t.getElementsByTagName("script")[0], n = t.createElement("script");
					n.type = "text/javascript", n.async = !0, n.src = "https://beacon-v2.helpscout.net", e.parentNode.insertBefore(n, e)
				}

				if (e.Beacon = n = function (t, n, a) {
					e.Beacon.readyQueue.push({method: t, options: n, data: a})
				}, n.readyQueue = [], "complete" === t.readyState) return a();
				e.attachEvent ? e.attachEvent("onload", a) : e.addEventListener("load", a, !1)
			}(window, document, window.Beacon || function () {
			});

			window.Beacon('init', this.beaconId);
		}
	},

	beaconShow: function () {
		window.Beacon('open');
	}
};

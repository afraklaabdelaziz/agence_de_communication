import SubscribersForm, {AjaxResponse} from './subscribersForm';

const { __ } = wp.i18n

export default class extends SubscribersForm {
	protected message = __('Exporting subscribers...', 'shopmagic-for-woocommerce');

	protected override showResponse(response: AjaxResponse): void {
		if (response.data.download) {
			window.location.href = response.data.download
		}
		super.showResponse(response)
	}
}

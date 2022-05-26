import SubscribersForm from './subscribersForm';

const { __ } = wp.i18n

export default class extends SubscribersForm {
	protected message = __('Import in progress...', 'shopmagic-for-woocommerce');
}

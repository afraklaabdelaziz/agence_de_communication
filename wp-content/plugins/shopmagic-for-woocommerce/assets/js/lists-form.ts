declare const shopmagic_form: { ajax_url: string; };
declare const wp: { i18n: { __: any; }; };

const { __ } = wp.i18n

interface AjaxResponse {
	success: boolean,
	data?: string
}

class ShortcodeForm {
	private readonly form: HTMLFormElement;
	private readonly messageContainer: HTMLElement;

	constructor(elementIdentifier: string) {
		const form = document.querySelector(elementIdentifier)
		if (!(form instanceof HTMLFormElement)) {
			throw new Error('Invalid element. Must be <form>');
		}
		this.form = form
		const messageContainer = this.form.querySelector<HTMLElement>('.shopmagic-message')
		if (!messageContainer) {
			throw new Error('Missing message container with class `.shopmagic-message`')
		}
		this.messageContainer = messageContainer
	}

	public init() {
		this.form.addEventListener('submit', this.handle)
	}

	private handle = async (e: Event): Promise<void> => {
		e.preventDefault()

		this.messageContainer.classList.add('hide')

		const response = await fetch(
			shopmagic_form.ajax_url,
			{
				method: 'POST',
				body: new FormData(this.form)
			}
		)

		if (!response.ok) {
			this.displayError(__('An error occurred during form submission. Try again later.', 'shopmagic-for-woocommerce'))
			return
		}

		const json = await response.json() as AjaxResponse;

		if (json.success) {
			if (typeof json.data === 'string') {
				this.displaySuccess(json.data)
			}
			return
		}

		if (typeof json.data === 'string') {
			this.displayError(json.data)
		}
	}

	private displaySuccess(message: string): void {
		this.displayMessage(message, 'success')
	}

	private displayError(message: string): void {
		this.displayMessage(message, 'error')

	}

	private displayMessage(message: string, type: 'success' | 'error'): void {
		if (type === 'success') {
			this.messageContainer.classList.remove('error')
			this.messageContainer.classList.add('success')
		} else {
			this.messageContainer.classList.remove('success')
			this.messageContainer.classList.add('error')
		}

		this.messageContainer.innerText = message
		this.messageContainer.classList.remove('hide')
	}
}

window.ShopMagic = window.ShopMagic || {}

window.ShopMagic.shortcodeForm = (elementIdentifier: string): void => {
	new ShortcodeForm(elementIdentifier).init()
}

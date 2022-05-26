const { __ } = wp.i18n

export interface AjaxResponse {
	success: boolean,
	data: {
		run?: string,
		context?: string
		download?: string
	}
}

export default abstract class {
	protected abstract message: string;

	protected constructor(private form: HTMLFormElement) {}

	public init() {
		this.form.addEventListener('submit', this.handle)
	}

	private handle = async (e: Event) => {
		e.preventDefault()

		this.messageBeforeRun();

		const response = await fetch(
			ShopMagic.ajaxurl,
			{
				method: 'POST',
				body: new FormData(this.form)
			}
		)

		if (!response.ok) {
			this.displayError(__('Network connection error', 'shopmagic-for-woocommerce'))
			return
		}
		let result = await response.json() as AjaxResponse
		if (!result.success) {
			this.displayError(result.data.context)
			return
		}

		result = await this.queueResponse(result)

		this.showResponse(result)
	}

	private async queueResponse(result: AjaxResponse): Promise<AjaxResponse> {
		if ( typeof result.data.run === 'undefined' ) {
			return result
		}

		const formData = new FormData(this.form);
		formData.append('run', result.data.run)

		const response = await fetch(
			ShopMagic.ajaxurl, {
				method: 'POST',
				body: formData
			})

		if (response.ok) {
			return await this.queueResponse(await response.json())
		}
	}

	private displayError(message: string): void {
		this.displayMessage(message, 'error')
	}

	private displaySuccess(message: string): void {
		this.displayMessage(message, 'success')
	}

	private displayMessage(message: string, type: 'error' | 'success') {
		const container = document.createElement('div')
		container.classList.add('shopmagic-message', type)

		const p = document.createElement('p')
		const messageNode = document.createTextNode(message)
		p.appendChild(messageNode)
		container.appendChild(p)

		this.form.append(container)
	}

	protected showResponse(response: AjaxResponse): void {
		this.form.querySelector('.progress')?.remove();

		if (typeof response.data.context === 'string') {
			this.displaySuccess(response.data.context)
		}
	}

	protected messageBeforeRun(): void {
		this.form.querySelectorAll('.shopmagic-message').forEach((el) => el.remove())

		const label = document.createElement('label')
		label.classList.add('progress', 'shopmagic-message')
		const progress = document.createElement('progress')
		const text = document.createTextNode(this.message)
		const br = document.createElement('br')
		label.append(text, br, progress)
		this.form.append(label)
	}
}

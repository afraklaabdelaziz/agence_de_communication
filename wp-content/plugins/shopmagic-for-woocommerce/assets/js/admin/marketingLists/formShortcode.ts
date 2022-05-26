type Parameter = string|boolean

class Shortcode implements Record<ParameterKey, Parameter> {
	public id = '0'
	public name = true
	public labels = true
	public doubleOptin = false
	public agreement = ''
}

type ParameterKey = keyof Shortcode

export default class FormShortcode {
	private container: HTMLElement;
	private output: HTMLOutputElement;
	private controls: NodeListOf<HTMLInputElement>;
	private readonly shortcode: Shortcode;

	public constructor(id: string) {
		const container = document.getElementById(id)
		if (!container) throw new Error('Invalid element reference')
		this.container = container

		const output = this.container.querySelector<HTMLOutputElement>('output[name="shortcode"]')
		if (!output) throw new Error('Container has no <output> element')
		this.output = output;
		this.controls = this.container.querySelectorAll('input, textarea')
		this.shortcode = parseShortcode(this.output.innerText)
	}

	public initialize(): void {
		this.controls.forEach((field) => {
			this.updateShortcode(field)

			field.addEventListener('change', () => {
				this.updateShortcode(field)
				this.renderShortcode()
			})
		})

		this.renderShortcode()
	}

	private updateShortcode = (field: HTMLInputElement): void => {
	/** @see https://github.com/microsoft/TypeScript/issues/31663#issuecomment-518854171 */
		if (field.type === 'checkbox') {
			(this.shortcode[fieldToParameter(field)] as boolean) = field.checked
		} else {
			(this.shortcode[fieldToParameter(field)] as string) = field.value
		}
	}

	private renderShortcode(): void {
		this.output.innerText = buildShortcode(this.shortcode)
	}
}

function fieldToParameter(field: HTMLInputElement): ParameterKey {
	return <ParameterKey>field.name.replace(/^_form_shortcode\[(.+)]$/, '$1')
}

function fromString(s: string): (p: ParameterKey) => Record<string, Parameter> {
	return (parameter: ParameterKey) => {
		if (parameter === 'id' || parameter === 'agreement') {
			return getParameter(parameter, s)
		}

		return hasBooleanParam(parameter, s)
	}
}

function getParameter(p: ParameterKey, shortcode: string): Record<string, Parameter> {
	const match = shortcode.match(new RegExp(` ${p}="(.+?)"`));
	if (match === null) return { [p]: '' }
	const [, value = ''] = match
	return { [p]: value }
}

function hasBooleanParam(p: ParameterKey, shortcode: string): Record<string, Parameter> {
	const match = shortcode.match(new RegExp(` ${p}=?(true|false)?`));
	if (match === null) return { [p]: Object.getOwnPropertyDescriptor(new Shortcode, p)?.value || false }
	const [, value = 'true'] = match
	return { [p]: value === 'true' }
}

export function parseShortcode(shortcodeString: string): Shortcode {
	const parser = fromString(shortcodeString)
	const shortcode = new Shortcode()
	for (const param of Object.keys(shortcode) as Array<ParameterKey>) {
		Object.assign(shortcode, parser(param))
	}
	return shortcode
}

export function buildShortcode(s: Shortcode): string {
	let shortcode = '[shopmagic_form'
	for (const param of Object.keys(s) as Array<ParameterKey>) {
		if (param === 'id' || param === 'agreement') {
			if (s[param] === '') continue
			shortcode += ` ${param}="${s[param]}"`
		} else {
			if (s[param]) {
				shortcode += ` ${param}`
			} else if (param !== 'doubleOptin') {
				shortcode += ` ${param}=false`
			}
		}
	}
	shortcode += ']'

	return shortcode
}

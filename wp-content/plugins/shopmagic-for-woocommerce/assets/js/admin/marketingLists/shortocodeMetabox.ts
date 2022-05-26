import FormShortcode from './formShortcode';

const OptTypeE = {
	In: 'opt_in',
	Out: 'opt_out'
} as const;

const metaboxSelector = 'shopmagic_form_metabox';

export default function () {
	const listTypeSelector = document.getElementById('type') as HTMLSelectElement;
	if (!listTypeSelector) return;

	new FormShortcode(metaboxSelector).initialize();
	maybeShowMetabox(listTypeSelector)
	listTypeSelector.addEventListener('change', () => {
		maybeShowMetabox(listTypeSelector)
	});
}

const maybeShowMetabox = (listElement: HTMLSelectElement): void => {
	if (listElement.value === OptTypeE.Out ) {
		document.getElementById(metaboxSelector).style.display = 'none'
	} else {
		document.getElementById(metaboxSelector).style.display = ''
	}
}

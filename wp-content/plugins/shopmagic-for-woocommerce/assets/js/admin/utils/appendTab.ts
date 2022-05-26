interface Tab {
	name: string,
	link: string,
	content?: string
}

function buildTabNavigation(tabs: Tab[]) {
	const tabNavigationContainer = document.createElement('div')
	tabNavigationContainer.classList.add('tabs', 'nav-tab-wrapper')

	const tabNavigationUl = document.createElement('ul')

	tabs.forEach(tab => {
		const tabNavigationLi = document.createElement('li')
		tabNavigationLi.classList.add('nav-tab-li')
		if (window.location.pathname + window.location.search === tab.link) {
			tabNavigationLi.classList.add('ui-tabs-active')
		}

		const tabNavigationA = document.createElement('a')
		tabNavigationA.classList.add('nav-tab')
		tabNavigationA.setAttribute('href', tab.link)
		tabNavigationA.appendChild(document.createTextNode(tab.name))

		tabNavigationLi.appendChild(tabNavigationA)
		tabNavigationUl.appendChild(tabNavigationLi)
	})

	tabNavigationContainer.appendChild(tabNavigationUl)
	return tabNavigationContainer;
}

export default function (root: HTMLElement, tabs: Tab[]) {
	const tabNavigation = buildTabNavigation(tabs);

	const wrapper = document.createElement('div')
	wrapper.id = 'super-duper'
	wrapInner(root, wrapper)
	prepend(wrapper, tabNavigation)
}

function wrapInner(root: HTMLElement, wrapper: HTMLElement) {
	root.parentNode.appendChild(wrapper)
	wrapper.appendChild(root)
}

function prepend(root: HTMLElement, before: HTMLElement) {
	root.insertBefore(before, root.firstChild)
}

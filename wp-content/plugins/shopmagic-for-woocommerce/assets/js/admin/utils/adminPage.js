const isPage = (postType, page = null) => {
  const currentPageParams = new URLSearchParams(window.location.search);
  return (currentPageParams.get('post_type') === postType)
      && (currentPageParams.get('page') === page)
      && (window.location.pathname.includes('edit.php'));
};

const isListPageFor = (postType) => isPage(postType);

export function isAutomationListPage() {
  return isListPageFor('shopmagic_automation');
}

export function isAutomationEditPage() {
  return document.getElementById('_shopmagic_edit_page') !== null;
}

export function isMarketingListsListPage() {
  return isListPageFor('shopmagic_list');
}

export function isSubscribersPage() {
  return isPage('shopmagic_automation', 'optins');
}

export function isSubscribersExportPage() {
  return isPage('shopmagic_automation', 'import-export');
}

export function isMarketingListsEditPage() {
  return document.getElementById('shopmagic_list_settings_metabox') !== null;
}

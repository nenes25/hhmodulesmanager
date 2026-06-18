$(function () {
  const changeGrid = new window.prestashop.component.Grid('hhmodulesmanager_change');

  changeGrid.addExtension(new window.prestashop.component.GridExtensions.FiltersResetExtension());
  changeGrid.addExtension(new window.prestashop.component.GridExtensions.SortingExtension());
  changeGrid.addExtension(new window.prestashop.component.GridExtensions.BulkActionCheckboxExtension());
  changeGrid.addExtension(new window.prestashop.component.GridExtensions.SubmitBulkActionExtension());
  changeGrid.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension());
  changeGrid.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension());
});

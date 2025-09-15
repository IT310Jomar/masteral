import type { VerticalNavItems } from '@/@layouts/types'

export default [
  {
    title: 'Dashboard',
    to: { name: 'index' },
    icon: { icon: 'tabler:layout-dashboard' },
    subject: 'SystemAndAdmin',
  },
  {
    title: 'Client List',
    to: { name: 'admin-client-clientList' },
    icon: { icon: 'tabler-users' },
    subject: 'SystemAndAdmin',
  },
  {
    title: 'Editor List',
    to: { name: 'admin-editor-editorList' },
    icon: { icon: 'tabler-users' },
    subject: 'SystemAndAdmin',
  },
  {
    title: 'Transaction List',
    icon: { icon: 'uil:transaction' },
    subject: 'SystemAndAdmin',
    action: 'manage',
    children: [
      {
        title: 'Paid Accounts',
        to: 'admin-request-paid_accounts',
        subject: 'SystemAndAdmin',
        action: 'manage',
      },
      {
        title: 'Uploaded Journal',
        to: 'admin-request-uploaded_journal',
        subject: 'SystemAndAdmin',
        action: 'manage',
      },
    ],
  },
  {
    title: 'Summary',
    icon: { icon: 'tabler:notebook' },
    subject: 'SystemAndAdmin',
    action: 'manage',
    children: [
      {
        title: 'Paid List',
        to: 'admin-summary-paidList',
        subject: 'SystemAndAdmin',
        action: 'manage',
      },
      {
        title: 'Sales Report',
        to: 'admin-summary-salesReport',
        subject: 'SystemAndAdmin',
        action: 'manage',
      },
    ],
  },
  {
    title: 'Approved for Editing',
    to: { name: 'admin-approved_editor-approved_for_editing' },
    icon: { icon: 'tabler:pencil-check' },
    subject: 'SystemAndAdmin',
  },
  {
    title: 'Published Journal',
    to: { name: 'admin-published-publishedJournal' },
    icon: { icon: 'tabler:book' },
    subject: 'SystemAndAdmin',
  },

  //CLIENT SIDEBAR LAYOUT
  {
    title: 'Dashboard',
    to: { name: 'index' },
    icon: { icon: 'tabler:layout-dashboard' },
    subject: 'SystemAndClient',
  },
  {
    title: 'Transaction',
    icon: { icon: 'icon-park-outline:transaction-order' },
    subject: 'SystemAndClient',
    action: 'manage',
    children: [
      {
        title: 'Payment',
        to: 'client-transaction-payment',
        subject: 'SystemAndClient',
        action: 'manage',
      },
      // {
      //   title: 'Upload Journal',
      //   to: 'client-transaction-upload-journal',
      //   subject: 'SystemAndClient',
      //   action: 'manage',
      // },
    ],
  },
  {
    title: 'My Request',
    icon: { icon: 'carbon:request-quote' },
    subject: 'SystemAndClient',
    action: 'manage',
    children: [
      {
        title: 'Pending',
        to: 'client-my-request-pending',
        subject: 'SystemAndClient',
        action: 'manage',
      },
      {
        title: 'Approved',
        to: 'client-my-request-approved',
        subject: 'SystemAndClient',
        action: 'manage',
      },
      {
        title: 'Rejected',
        to: 'client-my-request-rejected',
        subject: 'SystemAndClient',
        action: 'manage',
      },
    ],
  },
  {
    title: 'Progress Request',
    to: { name: 'client-progress_request' },
    icon: { icon: 'streamline:interface-page-controller-loading-1-progress-loading-load-wait-waiting' },
    subject: 'SystemAndClient',
  },
  {
    title: 'Published Journal',
    icon: { icon: 'ic:sharp-published-with-changes' },
    subject: 'SystemAndClient',
    action: 'manage',
    children: [
      {
        title: 'Download Document',
        to: 'client-publish_journal-download_docs',
        subject: 'SystemAndClient',
        action: 'manage',
      },
    ],
  },


  //EDITOR SIDEBAR LAYOUT
  {
    title: 'Dashboard',
    to: { name: 'index' },
    icon: { icon: 'tabler:layout-dashboard' },
    subject: 'SystemAndEditor',
  },
  {
    title: 'Assigned Documents',
    to: { name: 'editor-approved_documents' },
    icon: { icon: 'healthicons:i-documents-accepted-outline' },
    subject: 'SystemAndEditor',
  },
  {
    title: 'Publish',
    icon: { icon: 'carbon:catalog-publish' },
    subject: 'SystemAndEditor',
    action: 'manage',
    children: [
      {
        title: 'Upload Document',
        to: 'editor-publish-upload_documents',
        subject: 'SystemAndEditor',
        action: 'manage',
      },
    ],
  },
  {
    title: 'Published Journals',
    to: { name: 'editor-published_journals' },
    icon: { icon: 'ic:sharp-published-with-changes' },
    subject: 'SystemAndEditor',
  },

] as VerticalNavItems

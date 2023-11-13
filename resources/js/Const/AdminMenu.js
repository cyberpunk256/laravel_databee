export default {
  items: [
    {
      title: 'グループ一覧',
      icon: 'mdi-account-group',
      to: '/admin/group',
      roles: [1]
    },
    {
      title: '管理者一覧',
      icon: 'mdi-account-tie-hat',
      to: '/admin/admin',
      roles: [1]
    },
    {
      title: 'ユーザー一覧',
      icon: 'mdi-account',
      to: '/admin/user',
      roles: [1]
    },
    {
      title: 'メディア一覧',
      icon: 'mdi-video',
      to: '/admin/media',
      roles: [1,2,3]
    },
    {
      title: 'キャプチャー一覧',
      icon: 'mdi-image',
      to: '/admin/capture',
      roles: [1]
    },
  ],
}

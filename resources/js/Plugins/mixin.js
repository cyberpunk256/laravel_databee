import 'vue-toastification/dist/index.css'
import { useToast } from 'vue-toastification'
import L from 'leaflet'
const toast = useToast()

export default {
  data: () => ({}),
  computed: {
    user() {
      return this.$page.props.auth.user
    },
    flash() {
      return this.$page.props.flash
    },
    constant() {
      return this.$page.props.constant
    },
  },
  methods: {
    show_toast(status = null, message = null) {
      if (this.flash.success) {
        toast.success(this.flash.success, { timeout: 1000 })
      } else if (this.flash.error) {
        toast.error(this.flash.error, { timeout: 1000 })
      } else {
        if (status) {
          if (status == 'success') {
            toast.success(message, { timeout: 1000 })
          } else {
            toast.error(message, { timeout: 1000 })
          }
        } else {
          toast.error('エラーが発生しました。', { timeout: 1000 })
        }
      }
    },
    get_path_url(path) {
      return this.constant.cloudfront_domain + '/' + path
    },
    getTextOfOption(options, value) {
      const option = options.find((x) => x.value == value)
      return option ? option.text : null
    },
    getYmdHiFromDTS(datetime_str, apply_limit = 0) {
      return moment(datetime_str, 'YYYY-MM-DD HH:mm:ss').add(apply_limit, 'days').format('YYYY年MM月DD日 HH:mm')
    },
    getStatusText(value) {
      switch (value) {
        case 0:
          return 'エンコード中'
        case 1:
          return 'エンコード完了'
        case 2:
          return 'エンコードエラー'
        default:
          return ''
      }
    },
    onZoomChange() {
      const self = this
      if (this.map) {
        const zoom = this.map.getZoom()
        this.map.eachLayer((layer) => {
          // Check if the layer is a marker
          if (layer instanceof L.GPX) {
            const gpxLayers = layer.getLayers()
            const weight = self.getLineWeightByZoom(zoom, self.constant.map.gpx.weight)
            gpxLayers[0].setStyle({
              weight: weight,
            })
          }
          if (layer instanceof L.Marker) {
            const size = self.getMarkerSizeByZoom(zoom, self.constant.map.marker.size)
            console.log('self.constant.map.marker.icon', self.constant.map.marker.icon)
            const newIcon = L.icon({
              iconUrl: self.constant.map.marker.icon,
              iconSize: [size, size],
            })
            layer.setIcon(newIcon)
          }
        })
      }
    },
    getMarkerSizeByZoom(zoom, value) {
      const calc_value = value * Math.pow(2, zoom - 18)
      return calc_value > 15 ? calc_value : 15
    },
    getLineWeightByZoom(zoom, value) {
      console.log('zoom', zoom)
      console.log('value', value)
      const calc_value = value * Math.pow(2, zoom - 30)
      return calc_value > 5 ? calc_value : 5
    },
  },
}

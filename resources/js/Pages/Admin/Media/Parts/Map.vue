<template>
  <div>
    <div id="map" ref="map" class="admin_map"></div>
    <v-dialog v-if="record.type == 1 && modal_video_url" v-model="modal" width="auto">
      <v-card class="vp_card">
        <v-btn icon="mdi-close" class="vp_close" @click="modal = false"></v-btn>
        <ThreeVideoPlayer :url="modal_video_url" />
      </v-card>
    </v-dialog>
    <v-dialog v-if="record.type == 2 && modal_image_url" v-model="modal" width="auto">
      <v-card class="vp_card">
        <v-btn icon="mdi-close" class="vp_close" @click="modal = false"></v-btn>
        <div class="vp_content">
          <v-img v-if="modal" lazy-src="/empty.png" max-width="1000" :src="modal_image_url"></v-img>
        </div>
      </v-card>
    </v-dialog>
    <v-dialog v-if="record.type == 3 && modal_image_url" v-model="modal" width="auto">
      <v-card class="vp_card">
        <v-btn icon="mdi-close" class="vp_close" @click="modal = false"></v-btn>
        <div class="vp_content">
          <Panorama v-if="modal" :url="modal_image_url"></Panorama>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import L from 'leaflet'
import 'leaflet-gpx'
import ThreeVideoPlayer from '@/Components/Admin/ThreeVideoPlayer.vue'
import Panorama from '@/Components/Panorama.vue'

export default {
  components: { ThreeVideoPlayer, Panorama },
  props: ['record'],
  data() {
    return {
      map: null,
      map_default_option: {
        view: [0, 0],
        pin: [0, 0],
        zoom: 1,
      },
      modal: false,
      modal_video_url: null,
      modal_image_url: null,
      gpxOptions: {
        async: true,
        marker_options: {
          startIconUrl: false,
          endIconUrl: false,
          shadowUrl: false,
        },
        gpx_options: {
          joinTrackSegments: false,
        },
        polyline_options: {
          color: 'blue', // Change the line color if needed
          weight: 10, // Set the thickness of the line
          opacity: 1,
          lineCap: 'round',
        },
      },
    }
  },
  async mounted() {
    const self = this
    const observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting && self.$refs.map) {
        self.onInit()
      }
    }, { threshold: [1] }
    )
    observer.observe(this.$refs.map)
  },
  methods: {
    onInit() {
      const self = this
      const map_options = {
        maxZoom: self.constant.map.max_zoom,
      }
      const gpxOptions = self.gpxOptions
      gpxOptions.polyline_options.weight = self.getLineWeightByZoom(
        self.constant.map.zoom, 
        self.constant.map.gpx.weight
      )
      const iconSize = self.getMarkerSizeByZoom(self.constant.map.zoom, self.constant.map.marker.size)
      const marker_icon = L.icon({
        iconUrl: this.constant.map.marker.icon, // Replace with the path to your image
        iconSize: [iconSize, iconSize], // Set the size of the icon
      })

      const map = L.map('map', map_options).setView(self.constant.map.view, self.constant.map.zoom).on('zoomend', self.onZoomChange)

      L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data &copy; <a href="http://www.osm.org">OpenStreetMap</a>',
        maxZoom: self.constant.map.max_zoom
      }).addTo(map)

      if (self.record.type == 1) {
        // video
        const gpx_url = self.get_path_url(self.record.gpx_path)
        new L.GPX(gpx_url, gpxOptions)
          .on('loaded', function (e) {
            const gpxLayer = e.target
            self.onFitBounds(map, gpxLayer.getBounds())
            gpxLayer.addTo(map)
          })
          .on('click', function () {
            self.onShowModal()
          })
      } else if (self.record.type == 2 || self.record.type == 3) {
        const coordinate =
          self.record.image_lat && self.record.image_long
            ? [self.record.image_lat, self.record.image_long]
            : self.constant.map.init_pos

        const marker = L.marker(coordinate, { icon: marker_icon, draggable: true })
          .addTo(map)
          .on('click', function () {
            self.onShowModal()
          })
        self.onFitBounds(map, coordinate);

        // マウススクロールイベントのリスナーを追加
        marker.on('dragstart', function (e) {})
        marker.on('dragend', function (e) {
          var newPosition = marker.getLatLng()
          self.$emit('update', newPosition)
        })
      }
      self.map = map
    },
    onFitBounds(map, bounds) {
      let mapBounds = map.getBounds();
      mapBounds.extend(bounds);
      map.fitBounds(mapBounds);
    },
    onShowModal() {
      if (this.record.type == 1) {
        this.modal_video_url = this.record.video_path
        this.modal = true
      } else {
        // image, panorama
        this.modal_image_url = this.get_path_url(this.record.image_path)
        this.modal = true
      }
    },
  },
}
</script>

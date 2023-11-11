<template>
  <v-app class="bg-grey-lighten-4">
    <div id="map" class="user_map">
    </div>
    <v-dialog
      v-model="modal"
      width="auto"
    >
      <v-card class="vp_card">
        <three-video-player 
          :capture="true"
          :url="get_path_url('tmp/4d1284cc-f89e-422a-8380-25c17e21db2f.mp4')"
        />
        <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
      </v-card>
    </v-dialog>
  </v-app>
</template>

<script>
import L from 'leaflet';
import 'leaflet-gpx';
import ThreeVideoPlayer from '@/Components/Admin/ThreeVideoPlayer.vue';
import Panorama from '@/Components/Admin/Panorama.vue';

export default {
  components: { ThreeVideoPlayer, Panorama },
  // props: ['type', 'items'],
  props: ['type'],
  data() {
    return {
      map: null,
      zoom: 2,
      center: [47.41322, -1.219482],
      modal: false,
      modal_vp_url: null,
      modal_image_url: null,
      items: [
        {
          type: 1,
          vp_path: "tmp/4d1284cc-f89e-422a-8380-25c17e21db2f.mp4",
          gpx_path: "tmp/b5f944ef-efb6-4518-b212-f2cc377d78e4.gpx"
        }
      ],
      view: [36.2048, 138.2529],
      gpxOptions: {
        async: true,
        marker_options: {
          startIconUrl: '/pin-icon-start.png',
          endIconUrl:   '/pin-icon-end.png',
          shadowUrl:    '/pin-shadow.png',
        },
        gpx_options: {
            joinTrackSegments: false
        }, 
      }
    };
  },
  mounted() {
    const self = this
    self.map = L.map('map').setView(this.view,6);
    L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: 'Map data &copy; <a href="http://www.osm.org">OpenStreetMap</a>'
    }).addTo(self.map)
    for (let i = 0; i < self.items.length; i++) {
      const item = self.items[i];
      if(item.type == 1) { // video
        const gpx_url = self.get_path_url(item.gpx_path)
        console.log('gpx_url', gpx_url)
        new L.GPX(gpx_url, this.gpxOptions)
          .on('loaded', self.onLoaded)
          .on('click', function() {
              self.onShowModal(item)
          });
      }
    }
  },
  methods: {
    onLoaded(e) {
      this.map.fitBounds(e.target.getBounds());
      e.target.addTo(this.map);
    },
    onShowModal(item) {
      if(item.type == 1) {
        this.modal_vp_url = this.get_path_url(item.vp_path)
        this.modal = true
      } else { // image, panorama
        self.modal_image_url = this.get_path_url(item.image_path)
        this.modal = true
      }
    }
  },
};
</script>
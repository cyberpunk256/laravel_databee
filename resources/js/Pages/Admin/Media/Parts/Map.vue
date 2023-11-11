<template>
  <div>
    <div id="map" class="admin_map"></div>
    <v-dialog v-if="type == 1 && modal_vp_url" v-model="modal" width="auto">
      <v-card class="vp_card">
        <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
        <three-video-player :url="modal_vp_url"/>
      </v-card>
    </v-dialog>
    <v-dialog v-if="type == 2 && modal_image_url" v-model="modal" width="auto">
      <v-card class="vp_card">
        <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
        <v-img max-width="1000" contain src="modal_image_url"></v-img>
      </v-card>
    </v-dialog>
    <v-dialog v-if="type == 3 && modal_image_url" v-model="modal" width="auto">
      <v-card class="vp_card">
        <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
          <panorama  :url="modal_image_url"></panorama>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import L from 'leaflet';
import 'leaflet-gpx';
import ThreeVideoPlayer from '@/Components/Admin/ThreeVideoPlayer.vue';
import Panorama from '@/Components/Admin/Panorama.vue';

export default {
  components: { ThreeVideoPlayer, Panorama },
  props: ['type', 'media'],
  data() {
    return {
      map: null,
      zoom: 2,
      center: [47.41322, -1.219482],
      modal: false,
      modal_vp_url: null,
      modal_image_url: null,
      media: {
        type: 1,
        vp_path: "tmp/4d1284cc-f89e-422a-8380-25c17e21db2f.mp4",
        gpx_path: "tmp/b5f944ef-efb6-4518-b212-f2cc377d78e4.gpx"
      },
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
    if(self.media.type == 1) { // video
      const gpx_url = self.get_path_url(self.media.gpx_path)
      console.log('gpx_url', gpx_url)
      new L.GPX(gpx_url, this.gpxOptions)
        .on('loaded', self.onLoaded)
        .on('click', function() {
            self.onShowModal(self.media)
        });
    }
  },
  methods: {
    onLoaded(e) {
      this.map.fitBounds(e.target.getBounds());
      e.target.addTo(this.map);
    },
    onShowModal(media) {
      if(media.type == 1) {
        this.modal_vp_url = this.get_path_url(media.vp_path)
        this.modal = true
      } else { // image, panorama
        self.modal_image_url = this.get_path_url(media.image_path)
        this.modal = true
      }
    }
  },
};
</script>
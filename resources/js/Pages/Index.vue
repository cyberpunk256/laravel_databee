<template>
  <v-app class="bg-grey-lighten-4">
    <div id="map" class="m_map"></div>
    <v-dialog
      v-model="modal"
      width="auto"
    >
      <v-card>
        <v-card-text>
          <three-video-player/>
        </v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="modal = false">CLOSE</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-app>
</template>

<script>
import L from 'leaflet';
import 'leaflet-gpx';
import ThreeVideoPlayer from '@/Components/ThreeVideoPlayer.vue';

export default {
  created () {
  },
  components: { ThreeVideoPlayer },
  props: {
  },
  data() {
    return {
      map: null,
      zoom: 2,
      center: [47.41322, -1.219482],
      modal: false,
      modalVideo: null,
      gpxs: [
        {
          url: "demo.gpx",
          video: "demo.mp4"
        },
      ],
      view: [36.2048, 138.2529],
      gpxOptions: {
        async: true,
        marker_options: {
          startIconUrl: 'pin-icon-start.png',
          endIconUrl:   'pin-icon-end.png',
          shadowUrl:    'pin-shadow.png',
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
    for (let i = 0; i < self.gpxs.length; i++) {
      const item = self.gpxs[i];
      new L.GPX(item.url, this.gpxOptions)
        .on('loaded', self.onLoaded)
        .on('click', function() {
          self.onShowModal(item)
        });
    }
  },
  methods: {
    onLoaded(e) {
      this.map.fitBounds(e.target.getBounds());
      e.target.addTo(this.map);
    },
    onShowModal(item) {
      this.modal = true
      this.modalVideo = item.video
    }
  },
};
</script>
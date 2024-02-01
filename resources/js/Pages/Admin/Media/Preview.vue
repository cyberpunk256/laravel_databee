<script setup>
import MapLayout from '@/Layouts/MapLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <MapLayout>
    <template v-slot:map>
      <div id="map" ref="map" class="fix_map"></div>
    </template>
    <template v-slot:action>
      <Link href="/admin/media" as="div">
        <v-btn icon="mdi-arrow-left" size="large"></v-btn>
      </Link>
    </template>
    <template v-slot:content>
      <v-dialog v-if="modal_type == 1 && modal_video_url" v-model="modal" width="auto">
        <v-card class="vp_card">
          <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
          <three-video-player :url="modal_video_url"/>
        </v-card>
      </v-dialog>
      <v-dialog v-if="modal_type == 2 && modal_image_url" v-model="modal" width="auto">
        <v-card class="vp_card">
          <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
          <div class="vp_content">
            <v-img 
              lazy-src="/empty.png"
              v-if="modal" 
              max-width="1000" 
              :src="modal_image_url"
            ></v-img>
          </div>
        </v-card>
      </v-dialog>
      <v-dialog v-if="modal_type == 3 && modal_image_url" v-model="modal" width="auto">
        <v-card class="vp_card">
          <v-btn icon="mdi-close" @click="modal = false" class="vp_close"></v-btn>
          <div class="vp_content">
            <panorama v-if="modal"  :url="modal_image_url"></panorama>
          </div>
        </v-card>
      </v-dialog>
    </template>
  </MapLayout>
</template>

<script>
import L from 'leaflet';
import 'leaflet-gpx';
import ThreeVideoPlayer from '@/Components/Admin/ThreeVideoPlayer.vue';
import Panorama from '@/Components/Panorama.vue';

export default {
  components: { ThreeVideoPlayer, Panorama },
  props: ['records'],
  data() {
    return {
      map: null,
      modal: false,
      modal_type: 1, // video
      modal_video_url: null,
      modal_image_url: null,
      bounds_sum: new L.LatLngBounds(),
      gpxOptions: {
        async: true,
        marker_options: {
            startIconUrl: false,
            endIconUrl: false,
            shadowUrl: false,
        },
        gpx_options: {
          joinTrackSegments: false
        }, 
        polyline_options: {
          color: 'blue', // Change the line color if needed
          weight: 12, // Set the thickness of the line
          opacity: 1,
          lineCap: 'round'
        }
      }
    };
  },
  mounted() {
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

      const init_pos = self.user.init_lat && self.user.init_long ? 
        [self.user.init_lat, self.user.init_long] :
        self.constant.map.view
      const gpxOptions = self.gpxOptions
      gpxOptions.polyline_options.weight = self.getLineWeightByZoom(
        self.constant.map.zoom, 
        self.constant.map.gpx.weight
      )
      const iconSize = self.getMarkerSizeByZoom(self.constant.map.zoom, self.constant.map.marker.size)
      const marker_icon = L.icon({
        iconUrl: self.constant.map.marker.icon, // Replace with the path to your image
        iconSize: [iconSize, iconSize], // Set the size of the icon
      });
      
      const map = L.map('map', map_options).setView(init_pos, self.constant.map.zoom)
        .on('zoomend', self.onZoomChange);
      L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data &copy; <a href="http://www.osm.org">OpenStreetMap</a>',
        maxZoom: self.constant.map.max_zoom
      }).addTo(map)
      for (let i = 0; i < self.records.length; i++) {
        const record = self.records[i];
        if(record.type == 1) { // video
          const gpx_url = self.get_path_url(record.gpx_path)
          new L.GPX(gpx_url, gpxOptions)
            .on('loaded', function(e) {
              self.onGpxLoaded(e, map)
            })
            .on('click', function() {
                self.onShowModal(record)
            });
        } else if(record.type == 2 || record.type == 3) {
          const coordinate = (record.image_lat && record.image_long) ? 
            [record.image_lat, record.image_long] : self.constant.map.pin
          
          const pin_marker = L.marker(coordinate, { icon: marker_icon, draggable: true })
            .addTo(map)
            .on('click', function() {
              self.onShowModal(record)
            });
          self.onFitBounds(map, coordinate);
        }
      }
      self.map = map
    },
    onFitBounds(map, bounds) {
      let mapBounds = map.getBounds();
      mapBounds.extend(bounds);
      map.fitBounds(mapBounds);
    },
    onGpxLoaded(e, map) {
      const self = this
      const gpxLayer = e.target;
      self.onFitBounds(map, gpxLayer.getBounds());
      gpxLayer.addTo(map);
    },
    onShowModal(record) {
      this.modal_type = record.type
      if(record.type == 1) {
        this.modal_video_url = record.media_path
        this.modal = true
      } else { // image, panorama
        this.modal_image_url = this.get_path_url(record.media_path)
        this.modal = true
      }
    },
  },
};
</script>
package main

import (
	"html/template"
	"log"
	"net/http"
	"os"
	"path/filepath"
	"strings"
)

var (
	dirs        = []string{"img1", "img2", "img3"}
	allowedExts = map[string]bool{
		".jpg": true, ".jpeg": true, ".png": true,
		".gif": true, ".webp": true,
	}
)

type PageData struct {
	Images []string
}

func main() {
	http.HandleFunc("/", galleryHandler)

	// Serve images statically
	for _, d := range dirs {
		http.Handle("/"+d+"/", http.StripPrefix("/"+d+"/", http.FileServer(http.Dir(d))))
	}

	log.Println("Server running at http://localhost:8080")
	log.Fatal(http.ListenAndServe(":8080", nil))
}

func galleryHandler(w http.ResponseWriter, r *http.Request) {
	var images []string

	for _, dir := range dirs {
		files, err := os.ReadDir(dir)
		if err != nil {
			continue
		}
		for _, file := range files {
			ext := strings.ToLower(filepath.Ext(file.Name()))
			if !file.IsDir() && allowedExts[ext] {
				images = append(images, "/"+dir+"/"+file.Name())
			}
		}
	}

	// Load the template from file
	tmpl, err := template.ParseFiles("templates/gallery.html")
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	data := PageData{Images: images}
	if err := tmpl.Execute(w, data); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
	}
}


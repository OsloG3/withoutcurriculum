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

// Text represents a text file with its filename and human-friendly display name
type Text struct {
	Filename    string
	DisplayName string
}

// PageData holds the data passed to templates
type PageData struct {
	Categories []string
	Texts      []Text
	Selected   string
	Content    string
}

var templates = template.Must(template.ParseFiles(
	"templates/texts.html",
	"templates/content.html",
	"templates/base.html",
	"templates/index.html",
	"templates/header.html",
	"templates/novels.html",
	"templates/gallery.html",
))

type ImagePageData struct {
	Images []string
}

func main() {
	http.HandleFunc("/gallery", galleryHandler)
	http.HandleFunc("/novels", novelsHandler)
	http.HandleFunc("/contacts", contactsHandler)
	http.Handle("/static/", http.StripPrefix("/static/", http.FileServer(http.Dir("static"))))
	http.HandleFunc("/", indexHandler)

	// Serve images statically
	for _, d := range dirs {
		http.Handle("/static/"+d+"/", http.StripPrefix("/static/"+d+"/", http.FileServer(http.Dir(d))))
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
				images = append(images, "/static/"+dir+"/"+file.Name())
			}
		}
	}

	data := ImagePageData{Images: images}
	if err := templates.ExecuteTemplate(w, "gallery.html", data); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
	}
}

func novelsHandler(w http.ResponseWriter, r *http.Request) {
	data := PageData{}
	err := templates.ExecuteTemplate(w, "novels.html", data)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
	}
}

func contactsHandler(w http.ResponseWriter, r *http.Request) {

	data := PageData{}

	content, err := os.ReadFile("static/contacts.txt")
	if err != nil {
		http.Error(w, "Cannot read text file", http.StatusInternalServerError)
		return
	}
	data.Content = string(content)

	if err := templates.ExecuteTemplate(w, "base.html", data); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
	}
}

func indexHandler(w http.ResponseWriter, r *http.Request) {
	category := r.URL.Query().Get("category")
	text := r.URL.Query().Get("text")

	data := PageData{
		Categories: []string{"storie_ita", "storie_rus", "storie_pol", "traduzioni_rus_ita", "traduzioni_spa_ita", "novels"},
		Selected:   category,
	}

	// If a category is selected, list text files
	if category != "" {

		if text != "" {
			content, err := os.ReadFile(filepath.Join("static", category, text))
			if err != nil {
				http.Error(w, "Cannot read text file", http.StatusInternalServerError)
				return
			}
			data.Content = string(content)
		} else {
			files, err := os.ReadDir(filepath.Join("static", category))
			if err != nil {
				http.Error(w, "Cannot read category folder", http.StatusInternalServerError)
				return
			}

			for _, f := range files {
				if !f.IsDir() && filepath.Ext(f.Name()) == ".txt" {
					displayName := strings.TrimSuffix(f.Name(), filepath.Ext(f.Name()))
					displayName = strings.ReplaceAll(displayName, "_", " ")
					data.Texts = append(data.Texts, Text{
						Filename:    f.Name(),
						DisplayName: displayName,
					})
				}
			}
		}
		if err := templates.ExecuteTemplate(w, "base.html", data); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
		}
	} else {
		if err := templates.ExecuteTemplate(w, "index.html", data); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
		}
	}
}

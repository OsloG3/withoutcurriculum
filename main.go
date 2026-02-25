package main

import (
	"html/template"
	"log"
	"net/http"
	"os"
	"path/filepath"
	"sort"
	"strings"

	"github.com/microcosm-cc/bluemonday"
)

var (
	dirs        = []string{"avif_nuove", "avif_pum", "avif_riflessi", "avif_oa", "avif_fpn"}
	allowedExts = map[string]bool{
		".avif": true,
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
	HContent   template.HTML
}

var templates = template.Must(template.ParseFiles(
	"templates/texts.html",
	"templates/content.html",
	"templates/base.html",
	"templates/index.html",
	"templates/header.html",
	"templates/novels.html",
	"templates/gallery.html",
	"templates/contacts.html",
	"templates/authors.html",
))

type ImagePageData struct {
	Images []string
}

func main() {
	http.HandleFunc("/gallery", galleryHandler)
	http.HandleFunc("/novels", novelsHandler)
	http.HandleFunc("/contacts", contactsHandler)
	http.HandleFunc("/authors", authorsHandler)
	http.Handle("/static/", http.StripPrefix("/static/", http.FileServer(http.Dir("static"))))
	http.HandleFunc("/", indexHandler)

	// Serve images statically
	for _, d := range dirs {
		http.Handle("/static/images/"+d+"/", http.StripPrefix("/static/images/"+d+"/", http.FileServer(http.Dir("static/images/"+d))))
	}

	log.Println("Server running at http://localhost:8080")
	log.Fatal(http.ListenAndServe(":8080", nil))
}

func galleryHandler(w http.ResponseWriter, r *http.Request) {
	var images []string

	for _, dir := range dirs {
		files, err := os.ReadDir("static/images/" + dir)
		if err != nil {
			continue
		}

		for _, file := range files {
			ext := strings.ToLower(filepath.Ext(file.Name()))
			if !file.IsDir() && allowedExts[ext] {
				images = append(images, "/static/images/"+dir+"/"+file.Name())
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

	if err := templates.ExecuteTemplate(w, "contacts.html", data); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
	}
}

func authorsHandler(w http.ResponseWriter, r *http.Request) {
	data := PageData{}

	if err := templates.ExecuteTemplate(w, "authors.html", data); err != nil {
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
			if category == "novels" || category == "author" {
				hContent, err := os.ReadFile(filepath.Join("static", category, text))
				if err != nil {
					http.Error(w, "Cannot read text file", http.StatusInternalServerError)
					return
				}
				data.HContent = template.HTML(string(hContent))
			} else {
				Content, err := os.ReadFile(filepath.Join("static", category, text))
				if err != nil {
					http.Error(w, "Cannot read text file", http.StatusInternalServerError)
					return
				}
				data.Content = string(Content)
			}
		} else {
			files, err := os.ReadDir(filepath.Join("static", category))
			if err != nil {
				http.Error(w, "Cannot read category folder", http.StatusInternalServerError)
				return
			}

			// For translation we want to sort by author which is at the end of the name
			if strings.HasPrefix(category, "traduzioni") {
				sort.Slice(files, func(i, j int) bool {
					return reverse(files[i].Name()) > reverse(files[j].Name())
				})
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

// ################################################################################################
// Helper functions                                                                                                                                                             ###
// ################################################################################################

func sanitizeContent(raw string) template.HTML {
	// Allow only simple formatting tags
	policy := bluemonday.UGCPolicy()
	policy.AllowElements("i", "b", "em", "strong")
	safe := policy.Sanitize(raw)

	// Mark safe for template rendering
	return template.HTML(safe)
}

// reverse a string
func reverse(s string) string {
	runes := []rune(s)
	for i, j := 0, len(runes)-1; i < j; i, j = i+1, j-1 {
		runes[i], runes[j] = runes[j], runes[i]
	}
	return string(runes)
}

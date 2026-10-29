import type { ReactNode } from "react";

// Configuration générique des entités CRUD du dashboard

export type FieldType =
  | "text" | "textarea" | "number" | "checkbox" | "select" | "date" | "email" | "url"
  | "richtext" | "image" | "gallery" | "member" | "tags";

export interface FieldConfig {
  key: string;
  label: string;
  type: FieldType;
  required?: boolean;
  options?: Array<[string, string]>;
  // N'affiche le champ que si la condition est vraie (ex : selon display_type)
  showIf?: (values: Record<string, any>) => boolean;
}

export interface ColumnConfig<T = any> {
  label: string;
  key?: string;
  render?: (item: T) => ReactNode;
}

export interface EntityConfig {
  key: string;
  title: string;
  singular: string;
  endpoint: string;
  idKey: "slug" | "id";
  columns: ColumnConfig[];
  fields: FieldConfig[];
  orderable?: boolean;
  // Faux : masque le bouton « Nouveau » (entités alimentées par le site,
  // ex : les messages du formulaire de contact).
  canCreate?: boolean;
}

const fmt = (v: string | null | undefined) =>
  v ? new Date(v).toLocaleString("fr-FR", { dateStyle: "medium", timeStyle: "short" }) : "—";

export const ENTITIES: Record<string, EntityConfig> = {
  articles: {
    key: "articles",
    title: "Actualités",
    singular: "une actualité",
    endpoint: "/articles",
    idKey: "slug",
    columns: [
      { label: "Titre", key: "title" },
      { label: "Catégorie", key: "category" },
      { label: "Publié", render: (a) => (a.is_published ? "Oui" : "Non") },
      { label: "Date", render: (a) => fmt(a.published_at) },
    ],
    fields: [
      { key: "title", label: "Titre", type: "text", required: true },
      { key: "category", label: "Catégorie", type: "text" },
      { key: "excerpt", label: "Extrait", type: "textarea" },
      { key: "content", label: "Contenu de l'article (rédacteur)", type: "richtext" },
      { key: "gallery", label: "Galerie de l'article", type: "gallery" },
      { key: "author_member", label: "Auteur (personnel du CNDT) — badge « Rédigé par »", type: "member" },
      { key: "cover_image", label: "Image de couverture", type: "image" },
      { key: "is_published", label: "Publié", type: "checkbox" },
      { key: "in_hero", label: "Afficher dans le carrousel de l'accueil", type: "checkbox" },
    ],
  },

  publications: {
    key: "publications",
    title: "Publications",
    singular: "une publication",
    endpoint: "/publications",
    idKey: "slug",
    columns: [
      { label: "Titre", key: "title" },
      { label: "Type", key: "type" },
      { label: "Publié", render: (p) => (p.is_published ? "Oui" : "Non") },
      { label: "Date", render: (p) => fmt(p.published_at) },
    ],
    fields: [
      { key: "title", label: "Titre", type: "text", required: true },
      {
        key: "type", label: "Type", type: "select",
        options: [
          ["rapport", "Rapport"], ["communique", "Communiqué"],
          ["revue", "Revue"], ["discours", "Discours"],
        ],
      },
      { key: "description", label: "Description", type: "textarea" },
      { key: "file_path", label: "Fichier (chemin ou URL)", type: "text" },
      { key: "cover_image", label: "Image de couverture", type: "image" },
      { key: "is_published", label: "Publié", type: "checkbox" },
    ],
  },

  albums: {
    key: "albums",
    title: "Galerie (albums)",
    singular: "un album",
    endpoint: "/albums",
    idKey: "slug",
    columns: [
      { label: "Album", key: "title" },
      { label: "Photos", render: (a) => `${a.photos?.length ?? 0} photo(s)` },
      { label: "Ordre", key: "position" },
    ],
    fields: [
      { key: "title", label: "Titre", type: "text", required: true },
      { key: "description", label: "Description", type: "textarea" },
      { key: "cover_image", label: "Image de couverture", type: "image" },
      { key: "position", label: "Position", type: "number" },
      { key: "photos", label: "Photos de l'album", type: "gallery" },
    ],
  },

  members: {
    key: "members",
    title: "Personnel",
    singular: "un membre",
    endpoint: "/members",
    idKey: "slug",
    columns: [
      { label: "Nom", key: "full_name" },
      { label: "Fonction", key: "role" },
      { label: "Affichage", render: (m) => (m.display_type === "table" ? "Tableau" : "Carte") },
      { label: "Niveau", key: "level" },
      { label: "Unité", key: "unit" },
      { label: "Visible", render: (m) => (m.is_visible ? "Oui" : "Non") },
    ],
    fields: [
      { key: "first_name", label: "Prénom", type: "text", required: true },
      { key: "last_name", label: "Nom", type: "text", required: true },
      {
        key: "display_type", label: "Type d'affichage sur la page Personnel", type: "select",
        options: [["card", "Carte hiérarchique (avec photo)"], ["table", "Tableau des personnels"]],
      },
      { key: "role", label: "Fonction", type: "text" },
      {
        key: "grade", label: "Grade de recherche", type: "select",
        options: [
          ["Attaché de Recherche", "Attaché de Recherche"],
          ["Chargé de Recherche", "Chargé de Recherche"],
          ["Maître de Recherche", "Maître de Recherche"],
          ["Directeur de Recherche", "Directeur de Recherche"],
        ],
      },
      { key: "speciality", label: "Spécialité / diplôme de recrutement", type: "text" },
      { key: "photo", label: "Photo", type: "image" },
      { key: "unit", label: "Unité / Commission", type: "text" },
      { key: "email", label: "Email", type: "email" },
      { key: "phone", label: "Téléphone", type: "text" },
      { key: "is_visible", label: "Visible sur le site", type: "checkbox" },
      // ----- Champs de la fiche individuelle (cartes hiérarchiques) -----
      {
        key: "level", label: "Niveau hiérarchique (ex : Direction)", type: "text",
        showIf: (v) => v.display_type !== "table",
      },
      {
        key: "degree", label: "Diplôme (affiché sur la carte)", type: "text",
        showIf: (v) => v.display_type !== "table",
      },
      {
        key: "since", label: "En fonction depuis (année — fiche individuelle)", type: "text",
        showIf: (v) => v.display_type !== "table",
      },
      {
        key: "biography", label: "Biographie (fiche individuelle)", type: "richtext",
        showIf: (v) => v.display_type !== "table",
      },
      {
        key: "interests", label: "Centres d'intérêts (fiche individuelle)", type: "tags",
        showIf: (v) => v.display_type !== "table",
      },
    ],
  },

  projects: {
    key: "projects",
    title: "Projets",
    singular: "un projet",
    endpoint: "/projects",
    idKey: "slug",
    columns: [
      { label: "Projet", key: "title" },
      { label: "Statut", key: "status" },
      { label: "Publié", render: (p) => (p.is_published ? "Oui" : "Non") },
      { label: "Début", key: "start_date" },
    ],
    fields: [
      { key: "title", label: "Titre", type: "text", required: true },
      {
        key: "status", label: "Statut", type: "select",
        options: [["a_venir", "À venir"], ["en_cours", "En cours"], ["termine", "Terminé"]],
      },
      { key: "summary", label: "Résumé", type: "textarea" },
      { key: "description", label: "Description", type: "textarea" },
      { key: "partner", label: "Partenaire", type: "text" },
      { key: "cover_image", label: "Image de couverture", type: "image" },
      { key: "link", label: "Lien externe (URL)", type: "url" },
      { key: "start_date", label: "Date de début", type: "date" },
      { key: "end_date", label: "Date de fin", type: "date" },
      { key: "is_published", label: "Publié", type: "checkbox" },
    ],
  },

  messages: {
    key: "messages",
    title: "Messages",
    singular: "un message",
    endpoint: "/messages",
    idKey: "id",
    // Messages reçus via le formulaire de contact du site : pas de création ici
    canCreate: false,
    columns: [
      { label: "Reçu le", render: (m) => fmt(m.created_at) },
      { label: "Nom", key: "name" },
      { label: "Email", key: "email" },
      { label: "Téléphone", key: "phone" },
      { label: "Objet", key: "subject" },
      {
        label: "Message", key: "message",
        render: (m) => (
          <span title={m.message ?? undefined} className="line-clamp-2 max-w-[22rem] block">
            {m.message}
          </span>
        ),
      },
      { label: "Lu", render: (m) => (m.is_read ? "Oui" : "Non") },
    ],
    fields: [
      { key: "name", label: "Nom", type: "text" },
      { key: "email", label: "Email", type: "email" },
      { key: "phone", label: "Téléphone", type: "text" },
      { key: "subject", label: "Objet", type: "text" },
      { key: "message", label: "Message", type: "textarea" },
      { key: "is_read", label: "Marqué comme lu", type: "checkbox" },
    ],
  },

};

export const SECTIONS = [
  { key: "overview", label: "Vue d'ensemble" },
  { key: "articles", label: "Actualités" },
  { key: "publications", label: "Publications" },
  { key: "albums", label: "Galerie" },
  { key: "members", label: "Personnel" },
  { key: "projects", label: "Projets" },
  { key: "messages", label: "Messages" },
  { key: "hero", label: "Carrousel accueil" },
];

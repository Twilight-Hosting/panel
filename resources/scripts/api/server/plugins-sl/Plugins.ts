export interface QueryParams {
    framework: string;
    tags: string[];
    page?: number;
    search?: string;
}

export interface InstalledPlugin {
    id:                number;
    plugin_framework:  string;
    plugin_version:    string;
    plugin_id:         string;
    server_id:         number;
    plugin_name:       string;
    file_names:        string[];
    plugin_icon:       string;
    created_at:        Date;
    updated_at:        Date;
}

export interface ExternalAuthor {
  id: number;
  username: string;
  displayName: string;
  avatarUrl: string;
  _count: {
    plugins: number;
  };
}

export interface ExternalReleaseAuthor {
  username: string;
  avatarUrl: string;
}

export interface ExternalAsset {
  name: string;
  downloadUrl: string;
  size: number;
  downloadCount: number;
}

export interface ExternalRelease {
  name: string;
  body: string;
  tagName: string;
  htmlUrl: string;
  publishedAt: string;
  author: ExternalReleaseAuthor;
  assets: ExternalAsset[];
  createdAt: string;
  updatedAt: string;
}

export interface ExternalTag {
  id: number;
  name: string;
  slug: string;
  icon: string;
  createdAt: string;
  updatedAt: string;
}

export interface ExternalDependencyPlugin {
  id: string;
  name: string;
  repository: string;
  author: {
    id: number;
    username: string;
    displayName: string;
    avatarUrl: string;
  };
}

export interface ExternalDependency {
  id: string;
  pluginId: string;
  type: "REQUIRED" | "OPTIONAL"; // or string if other types exist
  dependsOn: string;
  createdAt: string;
  updatedAt: string;
  dependency: ExternalDependencyPlugin;
}

export interface ExternalPlugin {
  id: string;
  name: string;
  icon: string | null;
  description: string;
  repository: string;
  repositoryId: number;
  authorId: number;
  state: string;
  stars: number;
  downloads: number;
  readme: string;
  releases: ExternalRelease[];
  repoCreatedAt: string;
  repoUpdatedAt: string;
  pinned: boolean;
  recommended: boolean;
  lastCheckedAt: string;
  createdAt: string;
  updatedAt: string;
  organizationId: number | null;
  author: ExternalAuthor;
  tags: ExternalTag[];
  upvotes: number;
  organization: null;
  dependencies: ExternalDependency[];
}
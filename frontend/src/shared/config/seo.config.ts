// SEO Configuration
export interface SEOConfig {
  site: {
    name: string
    url: string
    description: string
    author: string
    keywords: string
  }
  social: {
    twitter: {
      site: string
      creator: string
    }
  }
  images: {
    defaultOgImage: string
    ogImageDimensions: {
      width: string
      height: string
      type: string
    }
  }
  theme: {
    color: string
    tileColor: string
  }
}

export const seoConfig: SEOConfig = {
  site: {
    name: "BAB'INSA",
    url: 'https://localhost:5173',
    description: 'Découvrez l\'univers du baby foot à l\'INSA de Rouen ! Suivez les matchs, consultez les classements et participez aux tournois étudiants. La passion du babyfoot se vit ici !',
    author: "BAB'INSA",
    keywords: 'insa, rouen, baby foot, babyfoot, matchs, tournoi, étudiants'
  },
  social: {
    twitter: {
      site: '@YourTwitter',
      creator: '@YourTwitter'
    }
  },
  images: {
    defaultOgImage: '/images/og-default.jpg',
    ogImageDimensions: {
      width: '1200',
      height: '630',
      type: 'image/jpeg'
    }
  },
  theme: {
    color: '#ffffff',
    tileColor: '#ffffff'
  }
}
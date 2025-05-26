import React from 'react'
import { Search } from 'lucide-react'
function LienHeader({val}) {
  return (
    <div className={val+' col-span-0 md:grid md:grid-cols-3 md:col-span-5 items-center gap-7'}>
                    {/* Search bar (desktop only) */}
                    <div className=" md:flex md:col-span-1 space-x-2 border-yellow-300 border-2 rounded px-3 py-1 mx-3">
                        <div className=' flex justify-beetwen'>
                            <Search className=" border-yellow-300" />
                            <input
                                type="text"
                                placeholder="Recherche de films..."
                                className="bg-transparent outline-none w-full text-sm text-yellow-800"
                            />
                        </div>
                    </div>
                    <div className='flex md:flex md:gap-5 md:col-span-2 gap-3 justify-center my-3 text-center md:m-0'>
                        <a className='hover:bg-red-600 text-white md:text-xl bg-green-700 rounded p-1 '>Accueil</a>
                        <a className='hover:bg-red-600 text-white bg-green-700 md:text-xl rounded p-1 '>Ofrres</a>
                        <a className='hover:bg-red-600 text-white bg-green-700 md:text-xl rounded p-1 '>Catégories</a>
                        <a className='hover:bg-red-600 text-white bg-green-700 md:text-xl rounded p-1 '>Produits</a>
                    </div>
                    
                </div>
  )
}

export default LienHeader
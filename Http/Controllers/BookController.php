<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Display list of books
    public function index(Request $request)
    {
        $query = Book::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
        }

        if ($request->has('genre') && $request->genre) {
            $query->where('genre', $request->genre);
        }

        if ($request->has('grade_level') && $request->grade_level) {
            $query->where('grade_level', $request->grade_level);
        }

        $books = $query->latest()->paginate(15);
        
        $genres = Book::distinct('genre')->pluck('genre');
        $gradeLevels = ['Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12', 'Grade 13'];

        return view('books.index', compact('books', 'genres', 'gradeLevels'));
    }

    // Show create form
    public function create()
    {
        $gradeLevels = ['Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12', 'Grade 13'];
        $genres = ['Fiction', 'Non-Fiction', 'Science', 'Mathematics', 'History', 'Geography', 'Literature', 'Art', 'Sports', 'Technology'];
        
        return view('books.create', compact('gradeLevels', 'genres'));
    }

    // Store new book
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'isbn' => 'nullable|string|unique:books',
            'author' => 'required|string|max:100',
            'publisher' => 'nullable|string|max:100',
            'genre' => 'required|string|max:50',
            'grade_level' => 'nullable|string|max:10',
            'total_copies' => 'required|integer|min:1',
            'location_shelf' => 'nullable|string|max:50',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable|string'
        ]);

        $validated['available_copies'] = $validated['total_copies'];

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('covers', 'public');
            $validated['cover_image'] = $path;
        }

        Book::create($validated);

        return redirect()->route('books.index')->with('success', 'Book added successfully!');
    }

    // Show single book
    public function show(Book $book)
    {
        return view('books.show', compact('book'));
    }

    // Show edit form
    public function edit(Book $book)
    {
        $gradeLevels = ['Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12', 'Grade 13'];
        $genres = ['Fiction', 'Non-Fiction', 'Science', 'Mathematics', 'History', 'Geography', 'Literature', 'Art', 'Sports', 'Technology'];
        
        return view('books.edit', compact('book', 'gradeLevels', 'genres'));
    }

    // Update book
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'isbn' => 'nullable|string|unique:books,isbn,' . $book->id,
            'author' => 'required|string|max:100',
            'publisher' => 'nullable|string|max:100',
            'genre' => 'required|string|max:50',
            'grade_level' => 'nullable|string|max:10',
            'total_copies' => 'required|integer|min:1',
            'location_shelf' => 'nullable|string|max:50',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable|string'
        ]);

        $diff = $validated['total_copies'] - $book->total_copies;
        $validated['available_copies'] = $book->available_copies + $diff;

        if ($request->hasFile('cover_image')) {
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $path = $request->file('cover_image')->store('covers', 'public');
            $validated['cover_image'] = $path;
        }

        $book->update($validated);

        return redirect()->route('books.index')->with('success', 'Book updated successfully!');
    }

    // Delete book
    public function destroy(Book $book)
    {
        if ($book->loans()->whereNull('return_date')->count() > 0) {
            return redirect()->route('books.index')
                ->with('error', 'Cannot delete: This book has active loans!');
        }

        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }
        
        $book->delete();

        return redirect()->route('books.index')->with('success', 'Book deleted successfully!');
    }
}